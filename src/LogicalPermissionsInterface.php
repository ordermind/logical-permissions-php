<?php

namespace Ordermind\LogicalPermissions;

interface LogicalPermissionsInterface {
  public function addType(string $name, callable $callback);
  public function removeType(string $name);
  public function getTypes();
  public function setTypes(array $types);
  public function getBypassCallback();
  public function setBypassCallback(callable $callback);
  public function checkAccess(array $permissions);
}

