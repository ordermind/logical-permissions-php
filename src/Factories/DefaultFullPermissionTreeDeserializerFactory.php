<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Factories;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicGates\LogicGateFactory;

/**
 * Factory to help create a default instance of a full permission tree deserializer.
 */
class DefaultFullPermissionTreeDeserializerFactory
{
    public function create(PermissionCheckerInterface ...$permissionCheckers): FullPermissionTreeDeserializer
    {
        return new FullPermissionTreeDeserializer(
            new PermissionTreeDeserializer(
                new PermissionCheckerLocator(...$permissionCheckers),
                new LogicGateNodeFactory(new LogicGateFactory())
            )
        );
    }

    /**
     * @param iterable<PermissionCheckerInterface> $permissionCheckers
     */
    public function createFromIterable(iterable $permissionCheckers): FullPermissionTreeDeserializer
    {
        return $this->create(...$permissionCheckers);
    }
}
