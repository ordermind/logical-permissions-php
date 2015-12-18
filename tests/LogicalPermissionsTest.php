<?php
 
use Ordermind\LogicalPermissions\LogicalPermissions;
 
class LogicalPermissionsTest extends PHPUnit_Framework_TestCase {
  
  /*-----------LogicalPermissions::addType()-------------*/
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddTypeParamNameMissing() {
    $lp = new LogicalPermissions();
    $lp->addType();
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddTypeParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType(0, function(){});
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddTypeParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->addType('', function(){});
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddTypeParamCallbackMissing() {
    $lp = new LogicalPermissions();
    $lp->addType('test');
  }
  
  /**
   * @expectedException InvalidArgumentException
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
   * @expectedException InvalidArgumentException
   */
  public function testRemoveTypeParamNameMissing() {
    $lp = new LogicalPermissions();
    $lp->removeType();
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testRemoveTypeParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->removeType(0);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testRemoveTypeParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->removeType('');
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
   * @expectedException InvalidArgumentException
   */
  public function testTypeExistsParamNameMissing() {
    $lp = new LogicalPermissions();
    $lp->typeExists();
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testTypeExistsParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->typeExists(0);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testTypeExistsParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->typeExists('');
  }
  
  public function testTypeExists() {
    $lp = new LogicalPermissions();
    $this->assertFalse($lp->typeExists('test'));
    $lp->addType('test', function(){});
    $this->assertTrue($lp->typeExists('test'));
  }
  
  /*------------LogicalPermissions::getTypeCallback()---------------*/
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetTypeCallbackParamNameMissing() {
    $lp = new LogicalPermissions();
    $lp->GetTypeCallback();
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetTypeCallbackParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->getTypeCallback(0);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetTypeCallbackParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->GetTypeCallback('');
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetTypeCallbackUnregisteredType() {
    $lp = new LogicalPermissions();
    $lp->getTypeCallback('test');
  }
  
  public function testGetTypeCallback() {
    $lp = new LogicalPermissions();
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
   * @expectedException InvalidArgumentException
   */
  public function testSetTypesParamTypesMissing() {
    $lp = new LogicalPermissions();
    $lp->setTypes();
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetTypesParamTypesWrongType() {
    $lp = new LogicalPermissions();
    $types = 55;
    $lp->setTypes($types);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetTypesParamNameWrongType() {
    $lp = new LogicalPermissions();
    $types = [function(){}];
    $lp->setTypes($types);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetTypesParamNameEmpty() {
    $lp = new LogicalPermissions();
    $types = ['' => function(){}];
    $lp->setTypes($types);
  }
  
  /**
   * @expectedException InvalidArgumentException
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
   * @expectedException InvalidArgumentException
   */
  public function testSetBypassCallbackParamCallbackMissing() {
    $lp = new LogicalPermissions();
    $lp->setBypassCallback();
  }

  /**
   * @expectedException InvalidArgumentException
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
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testCheckAccessParamPermissionsMissing() {
    $lp = new LogicalPermissions();
    $lp->checkAccess();
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testCheckAccessParamPermissionsWrongType() {
    $lp = new LogicalPermissions();
    $lp->checkAccess(0, []);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testCheckAccessParamPermissionsWrongPermissionType() {
    $lp = new LogicalPermissions();
    $types = [
      'flag' => function($flag, $context) {
        if($flag === 'never_bypass') {
          return !empty($context['user']['never_bypass']); 
        }
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'flag' => TRUE,
    ];
    $lp->checkAccess($permissions, []);
  }

  public function testCheckAccessParamPermissionsNestedTypes() {
    $lp = new LogicalPermissions();
    
    //Directly nested
    $permissions = [
      'flag' => [
        'flag' => 'testflag',
      ],
    ];
    
    $caught = FALSE;
    try {
      $lp->checkAccess($permissions, []);
    }
    catch(Exception $e) {
      $this->assertEquals(get_class($e), 'Exception'); 
      $caught = TRUE;
    }
    $this->assertTrue($caught);
    
    //Indirectly nested
    $permissions = [
      'flag' => [
        'OR' => [
          'flag' => 'testflag',
        ],
      ],
    ];
    
    $caught = FALSE;
    try {
      $lp->checkAccess($permissions, []);
    }
    catch(Exception $e) {
      $this->assertEquals(get_class($e), 'Exception'); 
      $caught = TRUE;
    }
    $this->assertTrue($caught);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testCheckAccessParamPermissionsUnregisteredType() {
    $lp = new LogicalPermissions();
    
    $permissions = [
      'flag' => 'testflag',
    ];
    $lp->checkAccess($permissions, []);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testCheckAccessParamContextMissing() {
    $lp = new LogicalPermissions();
    $lp->checkAccess([]);
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testCheckAccessParamContextWrongType() {
    $lp = new LogicalPermissions();
    $lp->checkAccess([], 0);
  }

  public function testCheckAccessBypassAccessAllow() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertTrue($lp->checkAccess([], []));
  }

  public function testCheckAccessBypassAccessDeny() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return FALSE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess([], []));
  }
  
  public function testCheckAccessNoBypassAccessBooleanAllow() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE; 
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertTrue($lp->checkAccess(['no_bypass' => FALSE], []));
  }

  public function testCheckAccessNoBypassAccessBooleanDeny() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE; 
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess(['no_bypass' => TRUE], []));
  }
  
  public function testCheckAccessNoBypassAccessArrayAllow() {
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
      'never_bypass' => FALSE,
    ];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
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
  
  public function testCheckAccessSingleItemAllow() {
    $lp = new LogicalPermissions();
    $types = [
      'flag' => function($flag, $context) {
        $access = FALSE;
        if($flag === 'testflag') {
          $access = !empty($context['user']['testflag']);
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'no_bypass' => [
        'flag' => 'never_bypass',
      ],
      'flag' => 'testflag',
    ];
    $user = [
      'id' => 1,
      'testflag' => TRUE,
    ];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessSingleItemDeny() {
    $lp = new LogicalPermissions();
    $types = [
      'flag' => function($flag, $context) {
        $access = FALSE;
        if($flag === 'testflag') {
          $access = !empty($context['user']['testflag']);
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'no_bypass' => [
        'flag' => 'never_bypass',
      ],
      'flag' => 'testflag',
    ];
    $user = [
      'id' => 1,
    ];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
  }

  public function testCheckAccessMultipleTypesShorthandOR() {
    $lp = new LogicalPermissions();
    $types = [
      'flag' => function($flag, $context) {
        $access = FALSE;
        if($flag === 'testflag') {
          $access = !empty($context['user']['testflag']);
        }
        return $access;
      },
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
      'misc' => function($item, $context) {
        $access = FALSE;
        $access = !empty($context['user'][$item]);
        return $access;
      }
    ];
    $lp->setTypes($types);
    $permissions = [
      'no_bypass' => [
        'flag' => 'never_bypass',
      ],
      'flag' => 'testflag',
      'role' => 'admin',
      'misc' => 'test',
    ];
    $user = [
      'id' => 1,
    ];
    //OR truth table
    //0 0 0
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 0 1
    $user['test'] = TRUE;
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 0
    $user['test'] = FALSE;
    $user['roles'] = ['admin'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 1
    $user['test'] = TRUE;
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 0
    $user = [
      'id' => 1,
      'testflag' => TRUE,
    ];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 1
    $user['test'] = TRUE;
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 0
    $user['test'] = FALSE;
    $user['roles'] = ['admin'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 1
    $user['test'] = TRUE;
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }

  public function testCheckAccessMultipleItemsShorthandOR() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => ['admin', 'editor'],
    ];
    $user = [
      'id' => 1,
    ];
    //OR truth table
    //0 0
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = [];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 1
    $user['roles'] = ['editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0
    $user['roles'] = ['admin'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1
    $user['roles'] = ['editor', 'admin'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }

  public function testCheckAccessMultipleItemsAND() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'AND' => [
          'admin', 
          'editor',
          'writer',
        ],
      ],
    ];
    $user = [
      'id' => 1,
    ];
    //AND truth table
    //0 0 0
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = [];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessMultipleItemsNAND() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'NAND' => [
          'admin', 
          'editor',
          'writer',
        ],
      ],
    ];
    $user = [
      'id' => 1,
    ];
    //NAND truth table
    //0 0 0
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = [];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessMultipleItemsOR() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'OR' => [
          'admin', 
          'editor',
          'writer',
        ],
      ],
    ];
    $user = [
      'id' => 1,
    ];
    //OR truth table
    //0 0 0
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = [];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessMultipleItemsNOR() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'NOR' => [
          'admin', 
          'editor',
          'writer',
        ],
      ],
    ];
    $user = [
      'id' => 1,
    ];
    //NOR truth table
    //0 0 0
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = [];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessMultipleItemsXOR() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'XOR' => [
          'admin', 
          'editor',
          'writer',
        ],
      ],
    ];
    $user = [
      'id' => 1,
    ];
    //XOR truth table
    //0 0 0
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = [];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  /**
   * @expectedException Exception
   */
  public function testCheckAccessMultipleItemsNOT() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'NOT' => [
          'admin', 
          'editor',
          'writer',
        ],
      ],
    ];
    $lp->checkAccess($permissions, []);
  }
  
  public function testCheckAccessSingleItemNOTString() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'NOT' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin', 'editor'],
    ];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    unset($user['roles']);
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessSingleItemNOTArray() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'NOT' => [
          'admin',
        ],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin', 'editor'],
    ];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    unset($user['roles']);
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessNestedLogic() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'OR' => [
          'NOT' => [
            'AND' => [
              'admin',
              'editor',
            ],
          ],
        ],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin', 'editor'],
    ];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    unset($user['roles']);
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['editor'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }
  
  public function testCheckAccessShorthandORMixedNumericStringKeys() {
    $lp = new LogicalPermissions();
    $types = [
      'role' => function($role, $context) {
        $access = FALSE;
        if(!empty($context['user']['roles'])) {
          $access = in_array($role, $context['user']['roles']); 
        }
        return $access;
      },
    ];
    $lp->setTypes($types);
    $permissions = [
      'role' => [
        'admin',
        'AND' => [
          'editor',
          'writer',
          'OR' => [
            'role1',
            'role2',
          ],
        ],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    unset($user['roles']);
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['editor'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['editor', 'writer'];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['editor', 'writer', 'role1'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['editor', 'writer', 'role2'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
  }
} 
