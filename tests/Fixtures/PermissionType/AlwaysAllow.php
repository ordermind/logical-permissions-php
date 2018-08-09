<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class AlwaysAllow implements PermissionTypeInterface {
  public static function getName() {
    return 'always_allow';
  }

  public function checkPermission($permission, $context) {
    return TRUE;
  }
}
