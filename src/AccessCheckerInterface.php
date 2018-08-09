<?php

namespace Ordermind\LogicalPermissions;

interface AccessCheckerInterface {
  /**
   * Sets the permission type collection.
   *
   * @param Ordermind\LogicalPermissions\PermissionTypeCollectionInterface $permissionTypeCollection
   *
   * @return Ordermind\LogicalPermissions\AccessCheckerInterface
   */
  public function setPermissionTypeCollection(\Ordermind\LogicalPermissions\PermissionTypeCollectionInterface $permissionTypeCollection);

  /**
   * Gets the permission type collection.
   *
   * @return Ordermind\LogicalPermissions\PermissionTypeCollectionInterface|NULL
   */
  public function getPermissionTypeCollection();

  /**
   * Sets the bypass access checker.
   *
   * @param Ordermind\LogicalPermissions\BypassAccessCheckerInterface $bypassAccessChecker
   *
   * @return Ordermind\LogicalPermissions\AccessCheckerInterface
   */
  public function setBypassAccessChecker(BypassAccessCheckerInterface $bypassAccessChecker);

  /**
   * Gets the bypass access checker.
   *
   * @return Ordermind\LogicalPermissions\BypassAccessCheckerInterface|NULL
   */
  public function getBypassAccessChecker();

  /**
  * Checks access for a permission tree.
  * @param array|string|bool $permissions The permission tree to be evaluated.
  * @param array|object $context (optional) A context that could for example contain the evaluated user and document. Default value is NULL.
  * @param bool $allow_bypass (optional) Determines whether bypassing access should be allowed. Default value is TRUE.
  * @return bool TRUE if access is granted or FALSE if access is denied.
  */
  public function checkAccess($permissions, $context = NULL, $allow_bypass = TRUE);

}

