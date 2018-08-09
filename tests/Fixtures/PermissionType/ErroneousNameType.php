<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class ErroneousNameType implements PermissionTypeInterface {
  public static function getName() {
    return 0;
  }

  public function checkPermission($permission, $context) {
    return TRUE;
  }
}
