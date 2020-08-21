<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicGates\LogicGateInputValueInterface as PermissionTreeNodeInterface;
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

    public function testResolve()
    {
        $context = [];

        $mockRootNode = $this->prophesize(PermissionTreeNodeInterface::class);
        $mockRootNode->getValue(null)->willReturn(true);
        $mockRootNode->getValue($context)->willReturn(true);
        $rootNode = $mockRootNode->reveal();

        $permissionTree = new PermissionTree($rootNode);

        $this->assertSame(true, $permissionTree->resolve());
        $this->assertSame(true, $permissionTree->resolve($context));
    }
}
