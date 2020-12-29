<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PermissionTreeTest extends TestCase
{
    use ProphecyTrait;

    public function testGetRootNode()
    {
        $mockRootNode = $this->prophesize(PermissionTreeNodeInterface::class);
        $rootNode = $mockRootNode->reveal();

        $permissionTree = new PermissionTree($rootNode);

        $this->assertSame($rootNode, $permissionTree->getRootNode());
    }

    public function testEvaluate()
    {
        $context = [];

        $mockRootNode = $this->prophesize(PermissionTreeNodeInterface::class);
        $mockRootNode->getValue(null)->willReturn(false);
        $mockRootNode->getValue($context)->willReturn(true);
        $rootNode = $mockRootNode->reveal();

        $permissionTree = new PermissionTree($rootNode);

        $this->assertSame(false, $permissionTree->evaluate());
        $this->assertSame(true, $permissionTree->evaluate($context));
    }
}
