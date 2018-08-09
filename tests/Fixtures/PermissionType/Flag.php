<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class Flag implements PermissionTypeInterface {
  public static function getName() {
    return 'flag';
  }

  public function checkPermission($permission, $context) {
    return !empty($context['user'][$permission]);
  }
}
