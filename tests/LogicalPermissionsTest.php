<?php
 
use Ordermind\LogicalPermissions\LogicalPermissions;
 
class LogicalPermissionsTest extends PHPUnit_Framework_TestCase {
  public function testCheckAccess() {
    $lp = new LogicalPermissions();
    $this->assertTrue($lp->checkAccess([]));
  }
} 
