<?php

namespace Ordermind\LogicalPermissions;

class PermissionArrayMixedTypesException extends Exception {
  public function __construct($permissions, $code = 0, Exception $previous = NULL) {
    $message = 'The same-level siblings in a permissions array must either be all numeric or all strings. Evaluated permissions: ' . print_r($permissions, TRUE);

    // make sure everything is assigned properly
    parent::__construct($message, $code, $previous);
  }
}