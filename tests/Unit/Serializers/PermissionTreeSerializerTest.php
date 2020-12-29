<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeNodeSerializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeSerializer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PermissionTreeSerializerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideTestSerialize
     */
    public function testSerialize(array $expected, $serializedNode)
    {
        $rootNode = $this->prophesize(PermissionTreeNodeInterface::class)->reveal();

        $mockPermissionTree = $this->prophesize(PermissionTree::class);
        $mockPermissionTree->getRootNode()->willReturn($rootNode);
        $permissionTree = $mockPermissionTree->reveal();

        $mockNodeSerializer = $this->prophesize(PermissionTreeNodeSerializer::class);
        $mockNodeSerializer->serialize($rootNode)->willReturn($serializedNode);
        $nodeSerializer = $mockNodeSerializer->reveal();

        $treeSerializer = new PermissionTreeSerializer($nodeSerializer);

        $this->assertSame($expected, $treeSerializer->serialize($permissionTree));
    }

    public function provideTestSerialize(): array
    {
        return [
            [['role' => 'admin'], ['role' => 'admin']],
            [[true], true],
        ];
    }
}
