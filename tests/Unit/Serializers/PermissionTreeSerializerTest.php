<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\StringPermission;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeSerializer;
use Ordermind\LogicGates\LogicGateEnum;
use Ordermind\LogicGates\LogicGateInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PermissionTreeSerializerTest extends TestCase
{
    use ProphecyTrait;

    public function testSerializeLogicGate()
    {
        $mockPermission = $this->prophesize(BooleanPermission::class);
        $mockPermission->getValue()->willReturn(true);

        $mockLogicGate = $this->prophesize(LogicGateInterface::class);
        $mockLogicGate->getName()->willReturn(LogicGateEnum::AND);
        $mockLogicGate->getInputValues()->willReturn([$mockPermission->reveal(), $mockPermission->reveal()]);
        $logicGate = $mockLogicGate->reveal();

        $mockPermissionTree = $this->prophesize(PermissionTree::class);
        $mockPermissionTree->getRootNode()->willReturn($logicGate);
        $permissionTree = $mockPermissionTree->reveal();

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
        $mockPermissionChecker = $this->prophesize(PermissionCheckerInterface::class);
        $mockPermissionChecker->getName()->willReturn('role');
        $permissionChecker = $mockPermissionChecker->reveal();

        $mockPermission = $this->prophesize(StringPermission::class);
        $mockPermission->getPermissionChecker()->willReturn($permissionChecker);
        $mockPermission->getPermissionValue()->willReturn('admin');
        $permission = $mockPermission->reveal();

        $mockPermissionTree = $this->prophesize(PermissionTree::class);
        $mockPermissionTree->getRootNode()->willReturn($permission);
        $permissionTree = $mockPermissionTree->reveal();

        $serializer = new PermissionTreeSerializer();
        $expectedResult = ['role' => 'admin'];
        $this->assertSame($expectedResult, $serializer->serialize($permissionTree));
    }

    public function testSerializeBooleanPermission()
    {
        $mockPermission = $this->prophesize(BooleanPermission::class);
        $mockPermission->getValue()->willReturn(true);
        $permission = $mockPermission->reveal();

        $mockPermissionTree = $this->prophesize(PermissionTree::class);
        $mockPermissionTree->getRootNode()->willReturn($permission);
        $permissionTree = $mockPermissionTree->reveal();

        $serializer = new PermissionTreeSerializer();
        $expectedResult = [true];
        $this->assertSame($expectedResult, $serializer->serialize($permissionTree));
    }
}
