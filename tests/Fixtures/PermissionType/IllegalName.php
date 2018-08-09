<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class IllegalName implements PermissionTypeInterface {
  public static function getName() {
    return 'and';
  }

  public function checkPermission($permission, $context) {
    return TRUE;
  }
}
