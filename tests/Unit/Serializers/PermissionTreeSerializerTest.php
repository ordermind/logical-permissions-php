<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\StringPermission;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeSerializer;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicGates\AndGate;
use PHPUnit\Framework\TestCase;

class PermissionTreeSerializerTest extends TestCase
{
    public function testSerializeLogicGate()
    {
        $permission = new BooleanPermission(true);
        $logicGate = new AndGate($permission, $permission);
        $permissionTree = new PermissionTree($logicGate);

        $serializer = new PermissionTreeSerializer();
        $expectedResult = [
            'AND' => [
                true,
                true,
            ],
        ];

        $this->assertSame($expectedResult, $serializer->serialize($permissionTree));
    }

    public function testSerializeStringPermission()
    {
        $permissionChecker = new RolePermissionChecker();
        $permission = new StringPermission($permissionChecker, 'admin');
        $permissionTree = new PermissionTree($permission);
        $serializer = new PermissionTreeSerializer();
        $expectedResult = ['role' => 'admin'];
        $this->assertSame($expectedResult, $serializer->serialize($permissionTree));
    }

    public function testSerializeBooleanPermission()
    {
        $permission = new BooleanPermission(true);
        $permissionTree = new PermissionTree($permission);
        $serializer = new PermissionTreeSerializer();
        $expectedResult = [true];
        $this->assertSame($expectedResult, $serializer->serialize($permissionTree));
    }
}
