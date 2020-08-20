<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Validators\NoBypassValidator;
use Ordermind\LogicGates\LogicGateFactory;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class AccessCheckerTest extends TestCase
{
    /**
     * @dataProvider checkAccessContextTypeProvider
     */
    public function testCheckAccessContextType(bool $expectException, $value)
    {
        $treeDeserializer = new PermissionTreeDeserializer(new PermissionCheckerLocator(), new LogicGateFactory());
        $accessChecker = new AccessChecker($treeDeserializer, new NoBypassValidator());

        if ($expectException) {
            $this->expectException(TypeError::class);
            $this->expectExceptionMessage('The context parameter must be an array or object');
        }

        $rawPermissionTree = new RawPermissionTree(false);

        $accessChecker->checkAccess($rawPermissionTree, $value);

        $this->addToAssertionCount(1);
    }

    public function checkAccessContextTypeProvider()
    {
        return [
            [false, null],
            [false, []],
            [false, new stdClass()],
            [true, 'string'],
            [true, 0],
        ];
    }
}
