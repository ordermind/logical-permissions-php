<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeNodeSerializer;
use Ordermind\LogicGates\LogicGateEnum;
use Ordermind\LogicGates\LogicGateInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PermissionTreeNodeSerializerTest extends TestCase
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
        $logicGateNode = new LogicGateNode($logicGate, []);

        $serializer = new PermissionTreeNodeSerializer();
        $expectedResult = [
            'AND' => [
                true,
                true,
            ],
        ];

        $this->assertSame($expectedResult, $serializer->serialize($logicGateNode));
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

        $serializer = new PermissionTreeNodeSerializer();
        $expectedResult = ['role' => 'admin'];
        $this->assertSame($expectedResult, $serializer->serialize($permission));
    }

    public function testSerializeBooleanPermission()
    {
        $mockPermission = $this->prophesize(BooleanPermission::class);
        $mockPermission->getValue()->willReturn(true);
        $permission = $mockPermission->reveal();

        $serializer = new PermissionTreeNodeSerializer();
        $expectedResult = true;
        $this->assertSame($expectedResult, $serializer->serialize($permission));
    }
}
