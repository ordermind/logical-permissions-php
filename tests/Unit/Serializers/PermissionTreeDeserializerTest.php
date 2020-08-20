<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicGates\LogicGateFactory;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnexpectedValueException;

class PermissionTreeDeserializerTest extends TestCase
{
    /**
     * @dataProvider illegalPermissionsProvider
     */
    public function testIllegalPermissions(
        string $expectedClass,
        string $expectedMessage,
        array $permissions
    ) {
        $locator = new PermissionCheckerLocator([new RolePermissionChecker(), new FlagPermissionChecker()]);
        $deserializer = new PermissionTreeDeserializer($locator, new LogicGateFactory());

        $this->expectException($expectedClass);
        $this->expectExceptionMessage($expectedMessage);
        $deserializer->deserialize($permissions);
    }

    public function illegalPermissionsProvider()
    {
        yield [
            TypeError::class,
            'A permission must either be a boolean, a string or an array. Evaluated permissions: 50',
            ['flag' => 50],
        ];

        foreach (
            [
                [['role' => [true]]],
                [['role' => [false]]],
                [['role' => ['TRUE']]],
                [['role' => ['true']]],
                [['role' => ['FALSE']]],
                [['role' => ['false']]],
            ] as $permissions
        ) {
            yield [
                UnexpectedValueException::class,
                'You cannot put a boolean permission as a descendant to a permission type',
                $permissions,
            ];
        }

        yield [
            UnexpectedValueException::class,
            'You cannot use an empty string in a permission tree.',
            [''],
        ];

        yield [
            UnexpectedValueException::class,
            'A string value cannot be used in a permission tree without having a permission type as an ancestor. '
                . 'Evaluated permissions: "illegal_value"',
            ['illegal_value'],
        ];

        yield [
            UnexpectedValueException::class,
            'The NO_BYPASS key must be placed highest in the permission hierarchy',
            ['OR' => ['no_bypass' => true]],
        ];

        foreach (
            [
                [['TRUE' => false]],
                [['TRUE' => []]],
                [['FALSE' => false]],
                [['FALSE' => []]],
                [['true' => false]],
                [['true' => []]],
                [['false' => false]],
                [['false' => []]],
            ] as $permissions
        ) {
            yield [
                UnexpectedValueException::class,
                'A boolean permission cannot have children',
                $permissions,
            ];
        }

        foreach (
            [
                ['flag' => ['flag' => 'testflag']],
                ['flag' => ['OR' => ['flag' => 'testflag']]],
            ] as $permissions
        ) {
            yield [
                UnexpectedValueException::class,
                'You cannot put a permission type as a descendant to another permission type',
                $permissions,
            ];
        }
    }

    public function testDeserializeParamPermissionsUnregisteredType()
    {
        $deserializer = new PermissionTreeDeserializer(new PermissionCheckerLocator(), new LogicGateFactory());

        $permissions = [
            'flag' => 'testflag',
        ];

        $this->expectException(PermissionTypeNotRegisteredException::class);
        $this->expectExceptionMessage('The permission type "flag" could not be found');
        $deserializer->deserialize($permissions);
    }
}
