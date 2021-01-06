<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\Factories;

use Ordermind\LogicalPermissions\Factories\LogicGateNodeFactory;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\PermissionCheckerLocatorInterface;
use Ordermind\LogicalPermissions\PermissionTypeCollection;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicGates\LogicGateFactory;

class PermissionTreeDeserializerFactory
{
    /**
     * Creates a permission tree deserializer instance.
     *
     * @param PermissionTypeCollection|null $locator
     *
     * @return PermissionTreeDeserializer
     */
    public static function create(?PermissionCheckerLocatorInterface $locator = null): PermissionTreeDeserializer
    {
        if (null === $locator) {
            $locator = new PermissionCheckerLocator();
        }

        $factory = new LogicGateNodeFactory(new LogicGateFactory());

        return new PermissionTreeDeserializer($locator, $factory);
    }
}
