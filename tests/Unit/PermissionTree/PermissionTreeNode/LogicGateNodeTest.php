<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicGates\AndGate;
use Ordermind\LogicGates\OrGate;
use PHPUnit\Framework\TestCase;

class LogicGateNodeTest extends TestCase
{
    public function testWrapsLogicGate()
    {
        $roleChecker = new RolePermissionChecker();

        $inputValues = [
            new BooleanPermission(true),
            new StringPermission($roleChecker, 'admin'),
            new LogicGateNode(new OrGate(
                new BooleanPermission(false),
                new BooleanPermission(true)
            )),
        ];

        $gate = new AndGate(...$inputValues);

        $logicGateNode = new LogicGateNode($gate);
        $context = ['user' => ['roles' => ['admin']]];

        $this->assertSame($gate->getName(), $logicGateNode->getName());
        $this->assertSame($inputValues, $logicGateNode->getInputValues());
        $this->assertSame($inputValues, $logicGateNode->getChildren());
        $this->assertSame($gate->execute($context), $logicGateNode->execute($context));
        $this->assertSame($gate->getValue($context), $logicGateNode->getValue($context));
    }
}
