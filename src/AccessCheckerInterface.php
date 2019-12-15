<?php

namespace Ordermind\LogicalPermissions;

interface AccessCheckerInterface
{
    /**
     * Sets the permission type collection.
     *
     * @param PermissionTypeCollectionInterface $permissionTypeCollection
     *
     * @return AccessCheckerInterface
     */
    public function setPermissionTypeCollection(PermissionTypeCollectionInterface $permissionTypeCollection);

    /**
     * Gets the permission type collection.
     *
     * @return PermissionTypeCollectionInterface|null
     */
    public function getPermissionTypeCollection();

    /**
     * Sets the bypass access checker.
     *
     * @param BypassAccessCheckerInterface $bypassAccessChecker
     *
     * @return AccessCheckerInterface
     */
    public function setBypassAccessChecker(BypassAccessCheckerInterface $bypassAccessChecker);

    /**
     * Gets the bypass access checker.
     *
     * @return BypassAccessCheckerInterface|null
     */
    public function getBypassAccessChecker();

    /**
     * Gets all keys that can be used in a permission tree.
     *
     * @return array Valid permission keys.
     */
    public function getValidPermissionKeys();

    /**
     * Checks access for a permission tree.
     * @param array|string|bool $permissions The permission tree to be evaluated.
     * @param array|object|null $context (optional) A context that could for example contain the evaluated user and document. Default value is NULL.
     * @param bool              $allowBypass (optional) Determines whether bypassing access should be allowed. Default value is TRUE.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied.
     */
    public function checkAccess($permissions, $context = null, $allowBypass = true);
}

