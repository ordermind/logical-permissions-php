<?php

namespace Ordermind\LogicalPermissions;

interface LogicalPermissionsInterface {
  public function addType($name, $callback);
  public function removeType($name);
  public function getTypes();
  public function setTypes($types);
  public function getBypassCallback();
  public function setBypassCallback($callback);
  public function checkAccess($permissions);
}

