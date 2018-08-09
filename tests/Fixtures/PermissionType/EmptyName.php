<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class EmptyName implements PermissionTypeInterface {
  public static function getName() {
    return '';
  }

  public function checkPermission($permission, $context) {
    return TRUE;
  }
}
