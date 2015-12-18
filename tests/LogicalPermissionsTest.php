<?php
declare(strict_types=1);
 
use Ordermind\LogicalPermissions\LogicalPermissions;
 
class LogicalPermissionsTest extends PHPUnit_Framework_TestCase {
  
  /*-----------LogicalPermissions::addType()-------------*/

  /**
   * @expectedException TypeError
   */
  public function testAddTypeParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType(0, function(){});
  }
  
  /**
   * @expectedException TypeError
   */
  public function testAddTypeParamCallbackWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType('test', 0);
  }
  
  public function testAddType() {
    $lp = new LogicalPermissions();
    $this->assertTrue($lp->addType('test', function(){}));
  }
  
  /*-------------LogicalPermissions::removeType()--------------*/
  
  /**
   * @expectedException TypeError
   */
  public function testRemoveTypeParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->removeType(0);
  }
  
  public function testRemoveTypeParamNameDoesntExist() {
    $lp = new LogicalPermissions();
    $this->assertFalse($lp->removeType('test'));
  }
  
  public function testRemoveType() {
    $lp = new LogicalPermissions();
    $lp->addType('test', function() {});
    $this->assertTrue($lp->removeType('test'));
  }
  
  /*------------LogicalPermissions::typeExists()---------------*/
  
  /**
   * @expectedException TypeError
   */
  public function testTypeExistsParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->typeExists(0);
  }
  
  public function testTypeExists() {
    $lp = new LogicalPermissions();
    $this->assertFalse($lp->typeExists('test'));
    $lp->addType('test', function(){});
    $this->assertTrue($lp->typeExists('test'));
  }
  
  /*------------LogicalPermissions::getTypeCallback()---------------*/
  
  /**
   * @expectedException TypeError
   */
  public function testGetTypeCallbackParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->getTypeCallback(0);
  }
  
  public function testGetTypeCallback() {
    $lp = new LogicalPermissions();
    $this->assertEquals($lp->getTypeCallback('test'), NULL);
    $callback = function(){};
    $lp->addType('test', function(){});
    $this->assertEquals($lp->getTypeCallback('test'), $callback);
  }
  
  /*------------LogicalPermissions::getTypes()---------------*/
  
  public function testGetTypes() {
    $lp = new LogicalPermissions();
    $this->assertEquals($lp->getTypes(), []);
    $type = ['test' => function(){}];
    $lp->addType('test', function(){});
    $this->assertEquals($lp->getTypes(), $type);
  }
  
  /*------------LogicalPermissions::setTypes()---------------*/
  
  /**
   * @expectedException TypeError
   */
  public function testSetTypesParamTypesWrongType() {
    $lp = new LogicalPermissions();
    $types = 55;
    $lp->setTypes($types);
  }

  /**
   * @expectedException TypeError
   */
  public function testSetTypesParamNameWrongType() {
    $lp = new LogicalPermissions();
    $types = [function(){}];
    $lp->setTypes($types);
  }
  
  /**
   * @expectedException TypeError
   */
  public function testSetTypesParamCallbackWrongType() {
    $lp = new LogicalPermissions();
    $types = ['test' => 'hej'];
    $lp->setTypes($types);
  }
  
  public function testSetTypes() {
    $lp = new LogicalPermissions();
    $types = ['test' => function(){}];
    $this->assertTrue($lp->setTypes($types));
    $this->assertEquals($lp->getTypes(), $types);
  }
  
  /*------------LogicalPermissions::getBypassCallback()---------------*/
  
  public function testGetBypassCallback() {
    $lp = new LogicalPermissions();
    $this->assertNull($lp->getBypassCallback());
  }
  
  /*------------LogicalPermissions::setBypassCallback()---------------*/

  /**
   * @expectedException TypeError
   */
  public function testSetBypassCallbackParamCallbackWrongType() {
    $lp = new LogicalPermissions();
    $lp->setBypassCallback('test');
  }
  
  public function testSetBypassCallback() {
    $lp = new LogicalPermissions();
    $callback = function(){};
    $this->assertTrue($lp->setBypassCallback($callback));
    $this->assertEquals($lp->getBypassCallback(), $callback);
  }
  
  /*------------LogicalPermissions::checkAccess()---------------*/

  public function testCheckAccessBypassAccessAllow() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertTrue($lp->checkAccess([]));
  }

  public function testCheckAccessBypassAccessDeny() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return FALSE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess([]));
  }

  public function testCheckAccessNoBypassAccessBooleanDeny() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE; 
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess(['no_bypass' => TRUE]));
  }

  public function testCheckAccessNoBypassAccessArrayDeny() {
    $lp = new LogicalPermissions();
    $types = [
      'flag' => function($flag, $context) {
        if($flag === 'never_bypass') {
          return !empty($context['user']['never_bypass']); 
        }
      },
    ];
    $lp->setTypes($types);
    $bypass_callback = function($context) { //Simulates for example that the user is a superuser with ability to bypass access
      return TRUE; 
    };
    $lp->setBypassCallback($bypass_callback);
    $permissions = [
      'no_bypass' => [
        'flag' => 'never_bypass',
      ],
    ];
    $user = [
      'id' => 1,
      'never_bypass' => TRUE,
    ];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccess() {
    $lp = new LogicalPermissions();
    //$this->assertTrue($lp->checkAccess([]));
    
    /*
    it('should test bypass_access allow', function () {
      var user = {bypass_access: TRUE};
      var permissions = {role: "admin"};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(TRUE);
    });
    it('should test bypass_access deny', function () {
      var user = {bypass_access: FALSE};
      var permissions = {role: "admin"};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(FALSE);
    });
    it('should test no_bypass boolean allow', function () {
      var user = {bypass_access: TRUE};
      var permissions = {role: ["admin"], no_bypass: FALSE};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(TRUE);
    });
    it('should test no_bypass boolean deny', function () {
      var user = {bypass_access: TRUE};
      var permissions = {role: ["admin"], no_bypass: TRUE};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(FALSE);
    });
    it('should test no_bypass object allow', function () {
      var user = {bypass_access: TRUE};
      var permissions = {role: ["admin"], no_bypass: {role: "superadmin"}};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(TRUE);
    });
    it('should test no_bypass object deny', function () {
      var user = {roles: ["superadmin"], bypass_access: TRUE};
      var permissions = {role: ["admin"], no_bypass: {role: "superadmin"}};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(FALSE);
    });
    it('should test single role allow', function () {
      var user = {_id: "user1", roles: ["editor", "sales"]};
      var permissions = {role: "sales"};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(TRUE);
    });
    it('should test single role deny', function () {
      var user = {_id: "user1", roles: ["editor", "admin"]};
      var permissions = {role: "sales"};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(FALSE);
    });
    it('should test multiple roles shorthand allow', function () {
      var user = {_id: "user1", roles: ["editor", "sales"]};
      var permissions = {role: ["admin", "sales"]};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(TRUE);
    });
    it('should test multiple roles shorthand deny', function () {
      var user = {_id: "user1", roles: ["editor"]};
      var permissions = {role: ["admin", "sales"]};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(FALSE);
    });
    it('should test flag has_account allow', function () {
      var user = {_id: "user1", roles: ["editor", "admin"]};
      var permissions = {flag: "has_account"};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(TRUE);
    });
    it('should test flag has_account deny', function () {
      var user = null;
      var permissions = {flag: "has_account"};
      expect(OrdermindLogicalPermissions.checkAccess(permissions, user)).toBe(FALSE);
    });
    */
  }
} 
