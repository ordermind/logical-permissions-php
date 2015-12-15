<?php

namespace Ordermind\LogicalPermissions;

interface LogicalPermissionsInterface {
  public function checkAccess($permissions);
}

