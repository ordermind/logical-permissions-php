<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use TypeError;

class FullPermissionTreeDeserializerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideTestThrowsExceptionOnIllegalPermissionsType
     */
    public function testThrowsExceptionOnIllegalPermissionsType(?string $expectedExceptionMessage, $input)
    {
        $mainTree = $this->prophesize(PermissionTree::class)->reveal();

        $mockTreeDeserializer = $this->prophesize(PermissionTreeDeserializer::class);
        $mockTreeDeserializer->deserialize($input)->willReturn($mainTree);
        $treeDeserializer = $mockTreeDeserializer->reveal();

        $fullTreeDeserializer = new FullPermissionTreeDeserializer($treeDeserializer);

        if ($expectedExceptionMessage) {
            $this->expectException(TypeError::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $fullTreeDeserializer->deserialize($input);

        $this->addToAssertionCount(1);
    }

    public function provideTestThrowsExceptionOnIllegalPermissionsType(): array
    {
        $expectedExceptionMessage = 'The permissions parameter must be an array or in certain cases a string or'
            . ' boolean. Evaluated permissions: ';

        return [
            [null, []],
            [null, 'true'],
            [null, true],
            [$expectedExceptionMessage . 'stdClass Object', new stdClass()],
            [$expectedExceptionMessage . '5', 5],
            [$expectedExceptionMessage, null],
        ];
    }

    /**
     * @dataProvider provideTestDeserialize
     */
    public function testDeserialize(bool $expectedHasNoBypassTree, $input)
    {
        $mockPermissionTree = $this->prophesize(PermissionTree::class);
        $permissionTree = $mockPermissionTree->reveal();

        $mockTreeDeserializer = $this->prophesize(PermissionTreeDeserializer::class);
        $mockTreeDeserializer->deserialize(Argument::any())->willReturn($permissionTree);
        $treeDeserializer = $mockTreeDeserializer->reveal();

        $fullTreeDeserializer = new FullPermissionTreeDeserializer($treeDeserializer);
        $fullTree = $fullTreeDeserializer->deserialize($input);

        $this->assertSame($permissionTree, $fullTree->getMainTree());
        $this->assertSame($expectedHasNoBypassTree, $fullTree->hasNoBypassTree());
        if ($expectedHasNoBypassTree) {
            $this->assertSame($permissionTree, $fullTree->getNoBypassTree());
        } else {
            $this->assertNull($fullTree->getNoBypassTree());
        }
    }

    public function provideTestDeserialize()
    {
        return [
            [false, true],
            [false, 'true'],
            [false, ['role' => 'admin']],
            [true, ['role' => 'admin', 'no_bypass' => true]],
            [true, ['role' => 'admin', 'NO_BYPASS' => true]],
        ];
    }
}
