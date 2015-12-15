<?php

namespace Ordermind\LogicalPermissions;
use Ordermind\LogicalPermissions\LogicalPermissionsInterface;

class LogicalPermissions implements LogicalPermissionsInterface {
  public function checkAccess($permission) {
    return true; 
  }
}

