<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FullPermissionTreeTest extends TestCase
{
    use ProphecyTrait;

    public function testGetMainTree()
    {
        $mockMainTree = $this->prophesize(PermissionTree::class);
        $mainTree = $mockMainTree->reveal();

        $fullTree = new FullPermissionTree([], $mainTree);
        $this->assertSame($mainTree, $fullTree->getMainTree());
    }

    public function testHasNoBypassTree()
    {
        $mockMainTree = $this->prophesize(PermissionTree::class);
        $mainTree = $mockMainTree->reveal();

        $mockNoBypassTree = $this->prophesize(PermissionTree::class);
        $noBypassTree = $mockNoBypassTree->reveal();

        $fullTree = new FullPermissionTree([], $mainTree);
        $this->assertFalse($fullTree->hasNoBypassTree());

        $fullTree = new FullPermissionTree([], $mainTree, null);
        $this->assertFalse($fullTree->hasNoBypassTree());

        $fullTree = new FullPermissionTree([], $mainTree, $noBypassTree);
        $this->assertTrue($fullTree->hasNoBypassTree());
    }

    public function testGetNoBypassTree()
    {
        $mockMainTree = $this->prophesize(PermissionTree::class);
        $mainTree = $mockMainTree->reveal();

        $mockNoBypassTree = $this->prophesize(PermissionTree::class);
        $noBypassTree = $mockNoBypassTree->reveal();

        $fullTree = new FullPermissionTree([], $mainTree);
        $this->assertNull($fullTree->getNoBypassTree());

        $fullTree = new FullPermissionTree([], $mainTree, null);
        $this->assertNull($fullTree->getNoBypassTree());

        $fullTree = new FullPermissionTree([], $mainTree, $noBypassTree);
        $this->assertSame($noBypassTree, $fullTree->getNoBypassTree());
    }
}
