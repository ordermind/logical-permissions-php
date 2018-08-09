<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class AlwaysDeny implements BypassAccessCheckerInterface {
  public function checkBypassAccess($context) {
    return FALSE;
  }
}
