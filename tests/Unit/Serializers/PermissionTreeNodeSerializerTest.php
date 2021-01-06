<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeNodeSerializer;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionTree\UnknownNodeType;
use Ordermind\LogicGates\AndGate;
use PHPUnit\Framework\TestCase;

class PermissionTreeNodeSerializerTest extends TestCase
{
    public function testSerializeLogicGate()
    {
        $node = new LogicGateNode(
            new AndGate(
                new BooleanPermission(true),
                new BooleanPermission(true)
            )
        );

        $serializer = new PermissionTreeNodeSerializer();

        $expectedResult = [
            'AND' => [
                true,
                true,
            ],
        ];
        $this->assertSame($expectedResult, $serializer->serialize($node));
    }

    public function testSerializeStringPermission()
    {
        $permissionChecker = new RolePermissionChecker();
        $permission = new StringPermission($permissionChecker, 'admin');
        $serializer = new PermissionTreeNodeSerializer();

        $expectedResult = ['role' => 'admin'];
        $this->assertSame($expectedResult, $serializer->serialize($permission));
    }

    public function testSerializeBooleanPermission()
    {
        $permission = new BooleanPermission(true);
        $serializer = new PermissionTreeNodeSerializer();

        $expectedResult = true;
        $this->assertSame($expectedResult, $serializer->serialize($permission));
    }

    public function testExceptionIsThrownOnUnknownNodeType()
    {
        $node = new UnknownNodeType();
        $serializer = new PermissionTreeNodeSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The serializer does not yet support the node type ' . UnknownNodeType::class);
        $serializer->serialize($node);
    }
}
