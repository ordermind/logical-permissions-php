<?php
 
use Ordermind\LogicalPermissions\LogicalPermissions;
 
class LogicalPermissionsTest extends PHPUnit_Framework_TestCase {
  public function testInit()
  {
    $lp = new LogicalPermissions;
    $this->assertTrue($lp->checkAccess([]));
  }
 
} 
