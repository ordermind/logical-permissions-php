<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyRegisteredException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;

interface PermissionCheckerLocatorInterface
{
    /**
     * Registers a permission checker.
     *
     * @param PermissionCheckerInterface $permissionChecker
     * @param bool                       $overwriteIfExists (optional) If the permission checker is already registered,
     *                                                      it will be overwritten if this parameter is set to TRUE. If
     *                                                      it is set to FALSE, PermissionTypeAlreadyRegisteredException
     *                                                      will be thrown. Default value is FALSE.
     *
     * @return self
     *
     * @throws PermissionTypeAlreadyRegisteredException
     */
    public function add(PermissionCheckerInterface $permissionChecker, bool $overwriteIfExists = false): self;

    /**
     * Unregisters a permission checker by name. If the permission checker cannot be found, nothing happens.
     *
     * @param string $name the name of the permission checker
     *
     * @return self
     */
    public function remove(string $name): self;

    /**
     * Checks if a permission checker is registered.
     *
     * @param string $name the name of the permission checker
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Gets a permission checker by name. If the permission checker is not registered,
     * PermissionTypeNotRegisteredException is thrown.
     *
     * @param string $name the name of the permission checker
     *
     * @return PermissionCheckerInterface
     *
     * @throws PermissionTypeNotRegisteredException
     */
    public function get(string $name): PermissionCheckerInterface;

    /**
     * Gets all registered permission checkers as an array, keyed by permission type.
     *
     * @return PermissionCheckerInterface[]
     */
    public function all(): array;

    /**
     * Gets all keys that may be used in a permission tree.
     *
     * @return string[]
     */
    public function getValidPermissionTreeKeys(): array;
}
