<?php

namespace Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class InvalidReturnType implements BypassAccessCheckerInterface {
  public function checkBypassAccess($context) {
    return 1;
  }
}
