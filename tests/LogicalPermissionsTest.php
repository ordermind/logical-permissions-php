<?php
declare(strict_types=1);
 
use Ordermind\LogicalPermissions\LogicalPermissions;
 
class LogicalPermissionsTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException     TypeError
   */
  public function testAddTypeParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType(1, function(){});
  }
  /**
   * @expectedException     TypeError
   */
  public function testAddTypeParamCallbackWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType('test', 0);
  }
  public function testCheckAccess() {
    $lp = new LogicalPermissions();
    $this->assertTrue($lp->checkAccess([]));
  }
} 
