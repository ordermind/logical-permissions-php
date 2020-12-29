<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Debug\AccessChecker;

use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeEvaluator;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeNodeValue;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeResult;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeNodeSerializer;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicGates\AndGate;
use Ordermind\LogicGates\OrGate;
use PHPUnit\Framework\TestCase;

class DebugPermissionTreeEvaluatorTest extends TestCase
{
    public function testEvaluate()
    {
        $roleChecker = new RolePermissionChecker();

        $gate = new AndGate(
            new BooleanPermission(true),
            new StringPermission($roleChecker, 'admin'),
            new LogicGateNode(new OrGate(
                new BooleanPermission(false),
                new BooleanPermission(true)
            ))
        );
        $permissionTree = new PermissionTree(new LogicGateNode($gate));

        $expected = new DebugPermissionTreeResult(
            true,
            new DebugPermissionTreeNodeValue(true, ['AND' => [true, ['role' => 'admin'], ['OR' => [false, true]]]]),
            new DebugPermissionTreeNodeValue(true, true),
            new DebugPermissionTreeNodeValue(true, ['role' => 'admin']),
            new DebugPermissionTreeNodeValue(true, ['OR' => [false, true]]),
            new DebugPermissionTreeNodeValue(false, false),
            new DebugPermissionTreeNodeValue(true, true)
        );

        $evaluator = new DebugPermissionTreeEvaluator(new PermissionTreeNodeSerializer());

        $this->assertEquals($expected, $evaluator->evaluate($permissionTree, ['user' => ['roles' => ['admin']]]));
    }
}
