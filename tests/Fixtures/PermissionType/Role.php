<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class Role implements PermissionTypeInterface {
  public static function getName() {
    return 'role';
  }

  public function checkPermission($permission, $context) {
    $access = FALSE;
    if(!empty($context['user']['roles'])) {
      $access = in_array($role, $context['user']['roles']);
    }

    return $access;
  }
}
