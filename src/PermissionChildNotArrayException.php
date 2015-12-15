<?php

namespace Ordermind\LogicalPermissions;

class PermissionChildNotArrayException extends Exception {
  public function __construct($type, $permissions, $code = 0, Exception $previous = NULL) {
    $message = "The child of a $type permission must be an array. Evaluated permissions: " . print_r($permissions, TRUE);

    // make sure everything is assigned properly
    parent::__construct($message, $code, $previous);
  }
}