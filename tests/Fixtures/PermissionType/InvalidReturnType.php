<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class InvalidReturnType implements PermissionTypeInterface {
  public static function getName() {
    return 'invalid_return_type';
  }

  public function checkPermission($permission, $context) {
    return 0;
  }
}
