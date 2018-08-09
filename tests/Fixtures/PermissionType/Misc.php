<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionType;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class Misc implements PermissionTypeInterface {
  public static function getName() {
    return 'misc';
  }

  public function checkPermission($permission, $context) {
    return !empty($context['user'][$permission]);
  }
}
