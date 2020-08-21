<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use TypeError;

class FullPermissionTreeDeserializerTest extends TestCase
{
    use ProphecyTrait;

    public function testDeserializeIllegalPermissions()
    {
        $mockTreeDeserializer = $this->prophesize(PermissionTreeDeserializer::class);
        $treeDeserializer = $mockTreeDeserializer->reveal();

        $fullTreeDeserializer = new FullPermissionTreeDeserializer($treeDeserializer);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('The permissions parameter must be an array or in certain cases a string or '
            . 'boolean. Evaluated permissions: stdClass Object');
        $fullTreeDeserializer->deserialize(new stdClass());
    }

    /**
     * @dataProvider provideTestDeserialize
     */
    public function testDeserialize(bool $expectedHasNoBypassTree, $input)
    {
        $mockPermissionTree = $this->prophesize(PermissionTree::class);
        $permissionTree = $mockPermissionTree->reveal();

        $mockTreeDeserializer = $this->prophesize(PermissionTreeDeserializer::class);
        $mockTreeDeserializer->deserialize(Argument::any())->willReturn($permissionTree);
        $treeDeserializer = $mockTreeDeserializer->reveal();

        $fullTreeDeserializer = new FullPermissionTreeDeserializer($treeDeserializer);
        $fullTree = $fullTreeDeserializer->deserialize($input);

        $this->assertSame($permissionTree, $fullTree->getMainTree());
        $this->assertSame($expectedHasNoBypassTree, $fullTree->hasNoBypassTree());
        if ($expectedHasNoBypassTree) {
            $this->assertSame($permissionTree, $fullTree->getNoBypassTree());
        } else {
            $this->assertNull($fullTree->getNoBypassTree());
        }
    }

    public function provideTestDeserialize()
    {
        return [
            [false, true],
            [false, 'true'],
            [false, ['role' => 'admin']],
            [true, ['role' => 'admin', 'no_bypass' => true]],
            [true, ['role' => 'admin', 'NO_BYPASS' => true]],
        ];
    }
}
