<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeSerializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeSerializer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FullPermissionTreeSerializerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideTestSerialize
     */
    public function testSerialize(
        array $expectedResult,
        bool $hasNoBypassTree,
        array $serializedMainTree,
        ?array $serializedNoBypassTree
    ) {
        $mockMainTree = $this->prophesize(PermissionTree::class);
        $mainTree = $mockMainTree->reveal();

        $mockNoBypassTree = $this->prophesize(PermissionTree::class);
        $noBypassTree = $mockNoBypassTree->reveal();

        $mockFullPermissionTree = $this->prophesize(FullPermissionTree::class);
        $mockFullPermissionTree->getMainTree()->willReturn($mainTree);
        $mockFullPermissionTree->hasNoBypassTree()->willReturn($hasNoBypassTree);
        $mockFullPermissionTree->getNoBypassTree()->willReturn($noBypassTree);
        $fullPermissionTree = $mockFullPermissionTree->reveal();

        $mockTreeSerializer = $this->prophesize(PermissionTreeSerializer::class);
        $mockTreeSerializer->serialize($mainTree)->willReturn($serializedMainTree);
        if ($serializedNoBypassTree) {
            $mockTreeSerializer->serialize($noBypassTree)->willReturn($serializedNoBypassTree);
        }
        $treeSerializer = $mockTreeSerializer->reveal();

        $fullTreeSerializer = new FullPermissionTreeSerializer($treeSerializer);
        $this->assertSame($expectedResult, $fullTreeSerializer->serialize($fullPermissionTree));
    }

    public function provideTestSerialize()
    {
        return [
            [[true], false, [true], null],
            [['role' => 'admin', 'NO_BYPASS' => [true]], true, ['role' => 'admin'], [true]],
        ];
    }
}
