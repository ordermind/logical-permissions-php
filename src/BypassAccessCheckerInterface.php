<?php

namespace Ordermind\LogicalPermissions;

interface BypassAccessCheckerInterface {
  /**
   * Checks if bypassing access checks should be allowed in the current context.
   *
   * @param array|object $context
   *
   * @return bool TRUE if bypassing access checks should be allowed or FALSE if it should not be allowed.
   */
  public function checkBypassAccess($context);
}
