<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class AlwaysAllow implements BypassAccessCheckerInterface {
  public function checkBypassAccess($context) {
    return TRUE;
  }
}
