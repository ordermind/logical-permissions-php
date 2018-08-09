<?php

namespace Ordermind\LogicalPermissions;

interface PermissionTypeInterface {
  /**
   * Gets the name of the permission type
   *
   * @return string The name of the permission type, for example "role"
   */
  public static function getName();

  /**
   * Checks if access should be granted for this permission in a given context
   *
   * @param string $permission The permission to check, for example "admin" if the permission type is "role".
   * The permission will always be a single string even if for example multiple roles are accepted. In that case this method will be called once for each role that is to be evaluated.
   *
   * @param array|object $context The context for evaluating the permission
   *
   * @return bool TRUE if access is granted or FALSE if access is not granted
   */
  public function checkPermission($permission, $context);
}
