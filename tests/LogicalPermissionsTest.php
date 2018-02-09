<?php

use Ordermind\LogicalPermissions\LogicalPermissions;

// Solves issue with different parent classes in different versions of PHPUnits
if(class_exists('PHPUnit_Framework_TestCase')) {
  class LogicalPermissionsPHPUnitShim extends PHPUnit_Framework_TestCase {}
}
else {
  class LogicalPermissionsPHPUnitShim extends PHPUnit\Framework\TestCase {}
}

class LogicalPermissionsTest extends LogicalPermissionsPHPUnitShim {

  /*-----------LogicalPermissions::addType()-------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testAddTypeParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType(0, function(){});
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testAddTypeParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->addType('', function(){});
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testAddTypeParamNameIsCoreKey() {
    $lp = new LogicalPermissions();
    $lp->addType('AND', function(){});
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException
   */
  public function testAddTypeParamNameExists() {
    $lp = new LogicalPermissions();
    $lp->addType('test', function(){});
    $lp->addType('test', function(){});
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testAddTypeParamCallbackWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType('test', 0);
  }

  public function testAddType() {
    $lp = new LogicalPermissions();
    $lp->addType('test', function(){});
    $this->assertTrue($lp->typeExists('test'));
  }

  /*-------------LogicalPermissions::removeType()--------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testRemoveTypeParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->removeType(0);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testRemoveTypeParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->removeType('');
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException
   */
  public function testRemoveTypeUnregisteredType() {
    $lp = new LogicalPermissions();
    $lp->removeType('test');
  }

  public function testRemoveType() {
    $lp = new LogicalPermissions();
    $lp->addType('test', function() {});
    $lp->removeType('test');
    $this->assertFalse($lp->typeExists('test'));
  }

  /*------------LogicalPermissions::typeExists()---------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testTypeExistsParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->typeExists(0);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
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
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testGetTypeCallbackParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->getTypeCallback(0);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testGetTypeCallbackParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->getTypeCallback('');
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException
   */
  public function testGetTypeCallbackUnregisteredType() {
    $lp = new LogicalPermissions();
    $lp->getTypeCallback('test');
  }

  public function testGetTypeCallback() {
    $lp = new LogicalPermissions();
    $callback = function(){};
    $lp->addType('test', $callback);
    $this->assertSame($lp->getTypeCallback('test'), $callback);
  }

  /*------------LogicalPermissions::setTypeCallback()---------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testSetTypeCallbackParamNameWrongType() {
    $lp = new LogicalPermissions();
    $lp->setTypeCallback(0, function(){});
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testSetTypeCallbackParamNameEmpty() {
    $lp = new LogicalPermissions();
    $lp->setTypeCallback('', function(){});
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException
   */
  public function testSetTypeCallbackUnregisteredType() {
    $lp = new LogicalPermissions();
    $lp->setTypeCallback('test', function(){});
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testSetTypeCallbackParamCallbackWrongType() {
    $lp = new LogicalPermissions();
    $lp->addType('test', function(){});
    $lp->setTypeCallback('test', 0);
  }

  public function testSetTypeCallback() {
    $lp = new LogicalPermissions();
    $lp->addType('test', function(){});
    $callback = function(){};
    $this->assertNotSame($lp->getTypeCallback('test'), $callback);
    $lp->setTypeCallback('test', $callback);
    $this->assertSame($lp->getTypeCallback('test'), $callback);
  }

  /*------------LogicalPermissions::getTypes()---------------*/

  public function testGetTypes() {
    $lp = new LogicalPermissions();
    $this->assertEquals($lp->getTypes(), []);
    $callback = function(){};
    $lp->addType('test', $callback);
    $types = $lp->getTypes();
    $this->assertEquals($types, ['test' => $callback]);
    $this->assertSame($types['test'], $callback);
  }

  /*------------LogicalPermissions::setTypes()---------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testSetTypesParamTypesWrongType() {
    $lp = new LogicalPermissions();
    $types = 55;
    $lp->setTypes($types);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testSetTypesParamTypesNameWrongType() {
    $lp = new LogicalPermissions();
    $types = [function(){}];
    $lp->setTypes($types);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testSetTypesParamTypesNameEmpty() {
    $lp = new LogicalPermissions();
    $types = ['' => function(){}];
    $lp->setTypes($types);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testSetTypesParamTypesNameIsCoreKey() {
    $lp = new LogicalPermissions();
    $types = ['no_bypass' => function(){}];
    $lp->setTypes($types);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testSetTypesParamTypesCallbackWrongType() {
    $lp = new LogicalPermissions();
    $types = ['test' => 'hej'];
    $lp->setTypes($types);
  }

  public function testSetTypes() {
    $lp = new LogicalPermissions();
    $callback = function(){};
    $lp->setTypes(['test' => $callback]);
    $types = $lp->getTypes();
    $this->assertEquals($types, ['test' => $callback]);
    $this->assertSame($types['test'], $callback);
  }

  /*------------LogicalPermissions::getBypassCallback()---------------*/

  public function testGetBypassCallback() {
    $lp = new LogicalPermissions();
    $this->assertNull($lp->getBypassCallback());
  }

  /*------------LogicalPermissions::setBypassCallback()---------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testSetBypassCallbackParamCallbackWrongType() {
    $lp = new LogicalPermissions();
    $lp->setBypassCallback('test');
  }

  public function testSetBypassCallback() {
    $lp = new LogicalPermissions();
    $callback = function(){};
    $lp->setBypassCallback($callback);
    $this->assertSame($lp->getBypassCallback(), $callback);
  }

  /*------------LogicalPermissions::getValidPermissionKeys()---------------*/

  public function testGetValidPermissionKeys() {
    $lp = new LogicalPermissions();
    $this->assertEquals($lp->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE']);
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
    $this->assertEquals($lp->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE', 'flag', 'role', 'misc']);
  }

  /*------------LogicalPermissions::checkAccess()---------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testCheckAccessParamPermissionsWrongType() {
    $lp = new LogicalPermissions();
    $lp->checkAccess(0, []);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testCheckAccessParamPermissionsWrongPermissionType() {
    $lp = new LogicalPermissions();
    $lp->addType('flag', function(){});
    $permissions = [
      'flag' => 50,
    ];
    $lp->checkAccess($permissions);
  }

  public function testCheckAccessParamPermissionsNestedTypes() {
    $lp = new LogicalPermissions();
    $lp->addType('flag', function(){});

    //Directly nested
    $permissions = [
      'flag' => [
        'flag' => 'testflag',
      ],
    ];

    $caught = FALSE;
    try {
      $lp->checkAccess($permissions);
    }
    catch(Exception $e) {
      $this->assertEquals(get_class($e), 'Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException');
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
      $lp->checkAccess($permissions);
    }
    catch(Exception $e) {
      $this->assertEquals(get_class($e), 'Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException');
      $caught = TRUE;
    }
    $this->assertTrue($caught);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException
   */
  public function testCheckAccessParamPermissionsUnregisteredType() {
    $lp = new LogicalPermissions();

    $permissions = [
      'flag' => 'testflag',
    ];
    $lp->checkAccess($permissions);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testCheckAccessParamContextWrongType() {
    $lp = new LogicalPermissions();
    $lp->checkAccess(FALSE, 0);
  }

  public function testCheckAccessBypassAccessCheckContextPassingArray() {
    $lp = new LogicalPermissions();
    $user = ['id' => 1];
    $bypass_callback = function($context) use ($user) {
      $this->assertTrue(isset($context['user']));
      $this->assertEquals($context['user'], $user);
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $lp->checkAccess(FALSE, ['user' => $user]);
  }

  public function testCheckAccessBypassAccessCheckContextPassingObject() {
    $lp = new LogicalPermissions();
    $user = ['id' => 1];
    $bypass_callback = function($context) use ($user) {
      $this->assertTrue(isset($context->user));
      $this->assertEquals($context->user, $user);
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $context = new stdClass();
    $context->user = $user;
    $lp->checkAccess(FALSE, $context);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testCheckAccessParamAllowBypassWrongType() {
    $lp = new LogicalPermissions();
    $lp->checkAccess(FALSE, [], 'test');
  }

  public function testCheckAccessEmptyArrayAllow() {
    $lp = new LogicalPermissions();
    $this->assertTrue($lp->checkAccess([]));
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidCallbackReturnTypeException
   */
  public function testCheckAccessBypassAccessWrongReturnType() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return 1;
    };
    $lp->setBypassCallback($bypass_callback);
    $lp->checkAccess(FALSE);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessBypassAccessIllegalDescendant() {
    $lp = new LogicalPermissions();
    $permissions = [
      'OR' => [
        'no_bypass' => true,
      ],
    ];
    $lp->checkAccess($permissions);
  }

  public function testCheckAccessBypassAccessAllow() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertTrue($lp->checkAccess(FALSE));
  }

  public function testCheckAccessBypassAccessDeny() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return FALSE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess(FALSE));
  }

  public function testCheckAccessBypassAccessDeny2() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess(FALSE, [], FALSE));
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessNoBypassWrongType() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $lp->checkAccess(['no_bypass' => 'test']);
  }

  public function testCheckAccessNoBypassEmptyPermissionsAllow() {
    $lp = new LogicalPermissions();
    $this->assertTrue($lp->checkAccess(['no_bypass' => TRUE]));
  }

  public function testCheckAccessNoBypassAccessBooleanAllow() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $permissions = ['no_bypass' => FALSE];
    $this->assertTrue($lp->checkAccess($permissions));
    //Test that permission array is not changed
    $this->assertTrue(isset($permissions['no_bypass']));
  }

  public function testCheckAccessNoBypassAccessBooleanDeny() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess(['no_bypass' => TRUE, FALSE], []));
  }

  public function testCheckAccessNoBypassAccessStringAllow() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $permissions = ['no_bypass' => 'False'];
    $this->assertTrue($lp->checkAccess($permissions));
    //Test that permission array is not changed
    $this->assertTrue(isset($permissions['no_bypass']));
  }

  public function testCheckAccessNoBypassAccessStringDeny() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) {
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);
    $this->assertFalse($lp->checkAccess(['no_bypass' => 'True', FALSE], []));
  }

  public function testCheckAccessNoBypassAccessArrayAllow() {
    $lp = new LogicalPermissions();
    $types = [
      'flag' => function($flag, $context) {
        $access = FALSE;
        if($flag === 'never_bypass') {
          $access = !empty($context['user']['never_bypass']);
        }
        return $access;
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
        $access = FALSE;
        if($flag === 'never_bypass') {
          $access = !empty($context['user']['never_bypass']);
        }
        return $access;
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
      FALSE,
    ];
    $user = [
      'id' => 1,
      'never_bypass' => TRUE,
    ];
    $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidCallbackReturnTypeException
   */
  public function testCheckAccessWrongPermissionCallbackReturnType() {
    $lp = new LogicalPermissions();
    $types = [
      'flag' => function($flag, $context) {
        $access = FALSE;
        if($flag === 'testflag') {
          $access = !empty($context['user']['testflag']);
        }
        return 0;
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
    $lp->checkAccess($permissions, ['user' => $user]);
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

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessANDWrongValueType() {
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
        'AND' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessANDTooFewElements() {
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
        'AND' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
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

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessNANDWrongValueType() {
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
        'NAND' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessNANDTooFewElements() {
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
        'NAND' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
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

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessORWrongValueType() {
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
        'OR' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessORTooFewElements() {
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
        'OR' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
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

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessNORWrongValueType() {
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
        'NOR' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessNORTooFewElements() {
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
        'NOR' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
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

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessXORWrongValueType() {
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
        'XOR' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessXORTooFewElements() {
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
        'XOR' => ['admin'],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
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
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessNOTWrongValueType() {
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
        'NOT' => TRUE,
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessNOTArrayTooFewElements() {
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
        'NOT' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
   */
  public function testCheckAccessNOTStringEmpty() {
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
        'NOT' => '',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $lp->checkAccess($permissions, ['user' => $user]);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException
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
    $lp->checkAccess($permissions);
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

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessBoolTRUEIllegalDescendant() {
    $lp = new LogicalPermissions();
    $permissions = [
      'role' => [TRUE],
    ];
    $lp->checkAccess($permissions);
  }

  public function testCheckAccessBoolTRUE() {
    $lp = new LogicalPermissions();
    $permissions = TRUE;
    $this->assertTrue($lp->checkAccess($permissions));
  }

  public function testCheckAccessBoolTRUEArray() {
    $lp = new LogicalPermissions();
    $permissions = [
      TRUE,
    ];
    $this->assertTrue($lp->checkAccess($permissions));
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessBoolFALSEIllegalDescendant() {
    $lp = new LogicalPermissions();
    $permissions = [
      'role' => [FALSE],
    ];
    $lp->checkAccess($permissions);
  }

  public function testCheckAccessBoolFALSE() {
    $lp = new LogicalPermissions();
    $permissions = FALSE;
    $this->assertFalse($lp->checkAccess($permissions));
  }

  public function testCheckAccessBoolFALSEArray() {
    $lp = new LogicalPermissions();
    $permissions = [
      FALSE,
    ];
    $this->assertFalse($lp->checkAccess($permissions));
  }

  public function testCheckAccessBoolFALSEBypass() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) { //Simulates for example that the user is a superuser with ability to bypass access
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);

    $permissions = [
      FALSE,
    ];
    $this->assertTrue($lp->checkAccess($permissions));
  }

  public function testCheckAccessBoolFALSENoBypass() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) { //Simulates for example that the user is a superuser with ability to bypass access
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);

    $permissions = [
      'no_bypass' => TRUE,
      FALSE,
    ];
    $this->assertFalse($lp->checkAccess($permissions));
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessStringTRUEIllegalChildrenSingleValue() {
    $lp = new LogicalPermissions();
    $permissions = [
      'TRUE' => FALSE,
    ];
    $lp->checkAccess($permissions);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessStringTRUEIllegalChildrenArray() {
    $lp = new LogicalPermissions();
    $permissions = [
      'TRUE' => [],
    ];
    $lp->checkAccess($permissions);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessStringTRUEIllegalDescendant() {
    $lp = new LogicalPermissions();
    $permissions = [
      'role' => ['TRUE'],
    ];
    $lp->checkAccess($permissions);
  }

  public function testCheckAccessStringTRUE() {
    $lp = new LogicalPermissions();
    $permissions = 'TRUE';
    $this->assertTrue($lp->checkAccess($permissions));
  }

  public function testCheckAccessStringTRUEArray() {
    $lp = new LogicalPermissions();
    $permissions = [
      'TRUE',
    ];
    $this->assertTrue($lp->checkAccess($permissions));
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessStringFALSEIllegalChildrenSingleValue() {
    $lp = new LogicalPermissions();
    $permissions = [
      'FALSE' => FALSE,
    ];
    $lp->checkAccess($permissions);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessStringFALSEIllegalChildrenArray() {
    $lp = new LogicalPermissions();
    $permissions = [
      'FALSE' => [],
    ];
    $lp->checkAccess($permissions);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testCheckAccessStringFALSEIllegalDescendant() {
    $lp = new LogicalPermissions();
    $permissions = [
      'role' => ['FALSE'],
    ];
    $lp->checkAccess($permissions);
  }

  public function testCheckAccessStringFALSE() {
    $lp = new LogicalPermissions();
    $permissions = 'FALSE';
    $this->assertFalse($lp->checkAccess($permissions));
  }

  public function testCheckAccessStringFALSEArray() {
    $lp = new LogicalPermissions();
    $permissions = [
      'FALSE',
    ];
    $this->assertFalse($lp->checkAccess($permissions));
  }

  public function testCheckAccessStringFALSEBypass() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) { //Simulates for example that the user is a superuser with ability to bypass access
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);

    $permissions = [
      'FALSE',
    ];
    $this->assertTrue($lp->checkAccess($permissions));
  }

  public function testCheckAccessStringFALSENoBypass() {
    $lp = new LogicalPermissions();
    $bypass_callback = function($context) { //Simulates for example that the user is a superuser with ability to bypass access
      return TRUE;
    };
    $lp->setBypassCallback($bypass_callback);

    $permissions = [
      'no_bypass' => TRUE,
      'FALSE',
    ];
    $this->assertFalse($lp->checkAccess($permissions));
  }
  public function testMixedBooleans() {
    $lp = new LogicalPermissions();

    $permissions = [
      'FALSE',
      'TRUE',
    ];
    $this->assertTrue($lp->checkAccess($permissions));

    $permissions = [
      'OR' => [
        'FALSE',
        'TRUE',
      ],
    ];
    $this->assertTrue($lp->checkAccess($permissions));

    $permissions = [
      'AND' => [
        'TRUE',
        'FALSE',
      ],
    ];
    $this->assertFalse($lp->checkAccess($permissions));
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
      FALSE,
      'FALSE',
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

  public function testCheckAccessLogicGateFirst() {
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
      'AND' => [
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
        TRUE,
        'TRUE',
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
