<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit;

use Ordermind\LogicalPermissions\DefaultFullPermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\Factories\LogicGateNodeFactory;
use Ordermind\LogicalPermissions\Locators\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicGates\LogicGateFactory;
use PHPUnit\Framework\TestCase;

class DefaultFullPermissionTreeDeserializerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $permissionCheckers = [new RolePermissionChecker(), new FlagPermissionChecker()];
        $factory = new DefaultFullPermissionTreeDeserializerFactory();
        $expected = new FullPermissionTreeDeserializer(
            new PermissionTreeDeserializer(
                new PermissionCheckerLocator(...$permissionCheckers),
                new LogicGateNodeFactory(new LogicGateFactory())
            )
        );
        $this->assertEquals($expected, $factory->create(...$permissionCheckers));
    }

    public function testCreateFromIterable()
    {
        $permissionCheckers = [new RolePermissionChecker(), new FlagPermissionChecker()];
        $factory = new DefaultFullPermissionTreeDeserializerFactory();
        $expected = new FullPermissionTreeDeserializer(
            new PermissionTreeDeserializer(
                new PermissionCheckerLocator(...$permissionCheckers),
                new LogicGateNodeFactory(new LogicGateFactory())
            )
        );
        $this->assertEquals($expected, $factory->createFromIterable($permissionCheckers));
    }
}
