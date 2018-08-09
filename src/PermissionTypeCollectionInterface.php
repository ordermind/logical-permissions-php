<?php

namespace Ordermind\LogicalPermissions;

interface PermissionTypeCollectionInterface {
  /**
   * Returns a PHP array representation of this collection.
   *
   * @return array
   */
  public function toArray();

  /**
   * Adds a permission type to the collection.
   *
   * @param Ordermind\LogicalPermissions\PermissionTypeInterface $permissionType
   * @param bool $overwriteIfExists (optional) If the permission type already exists in the collection, it will be overwritten if this parameter is set to TRUE.
   * If it is set to FALSE, Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException will be thrown. Default value is FALSE.
   *
   * @return Ordermind\LogicalPermissions\PermissionTypeCollectionInterface
   */
  public function add(\Ordermind\LogicalPermissions\PermissionTypeInterface $permissionType, $overwriteIfExists = FALSE);

  /**
   * Removes a permission type by name from the collection. If the permission type cannot be found in the collection, nothing happens.
   *
   * @param string $name The name of the permission type.
   *
   * @return Ordermind\LogicalPermissions\PermissionTypeCollectionInterface
   */
  public function remove($name);

  /**
   * Gets a permission type by name. If the permission type cannot be found, NULL is returned.
   *
   * @param string $name The name of the permission type.
   *
   * @return Ordermind\LogicalPermissions\PermissionTypeInterface|NULL
   */
  public function get($name);

  /**
   * Checks if a permission type exists in the collection.
   *
   * @param string $name The name of the permission type.
   *
   * @return bool
   */
  public function has($name);

  /**
   * Gets all keys that can be used in a permission tree.
   *
   * @return array Valid permission keys.
   */
  public function getValidPermissionKeys();
}
