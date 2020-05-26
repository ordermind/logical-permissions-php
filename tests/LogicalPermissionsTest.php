<?php

use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidCallbackReturnTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\LogicalPermissions;

// Solves issue with different parent classes in different versions of PHPUnits
if (class_exists('PHPUnit_Framework_TestCase')) {
    class LogicalPermissionsPHPUnitShim extends PHPUnit_Framework_TestCase
    {
    }
} else {
    class LogicalPermissionsPHPUnitShim extends PHPUnit\Framework\TestCase
    {
    }
}

class LogicalPermissionsTest extends LogicalPermissionsPHPUnitShim
{
    // -----------LogicalPermissions::addType()-------------

    public function testAddTypeParamNameWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->addType(0, function () {
        });
    }

    public function testAddTypeParamNameEmpty()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentValueException::class);
        $lp->addType('', function () {
        });
    }

    public function testAddTypeParamNameIsCoreKey()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentValueException::class);
        $lp->addType('AND', function () {
        });
    }

    public function testAddTypeParamNameExists()
    {
        $lp = new LogicalPermissions();
        $lp->addType('test', function () {
        });

        $this->expectException(PermissionTypeAlreadyExistsException::class);
        $lp->addType('test', function () {
        });
    }

    public function testAddTypeParamCallbackWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->addType('test', 0);
    }

    public function testAddType()
    {
        $lp = new LogicalPermissions();
        $lp->addType('test', function () {
        });
        $this->assertTrue($lp->typeExists('test'));
    }

    // -------------LogicalPermissions::removeType()--------------

    public function testRemoveTypeParamNameWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->removeType(0);
    }

    public function testRemoveTypeParamNameEmpty()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentValueException::class);
        $lp->removeType('');
    }

    public function testRemoveTypeUnregisteredType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(PermissionTypeNotRegisteredException::class);
        $lp->removeType('test');
    }

    public function testRemoveType()
    {
        $lp = new LogicalPermissions();
        $lp->addType('test', function () {
        });
        $lp->removeType('test');
        $this->assertFalse($lp->typeExists('test'));
    }

    // ------------LogicalPermissions::typeExists()---------------

    public function testTypeExistsParamNameWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->typeExists(0);
    }

    public function testTypeExistsParamNameEmpty()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentValueException::class);
        $lp->typeExists('');
    }

    public function testTypeExists()
    {
        $lp = new LogicalPermissions();
        $this->assertFalse($lp->typeExists('test'));
        $lp->addType('test', function () {
        });
        $this->assertTrue($lp->typeExists('test'));
    }

    // ------------LogicalPermissions::getTypeCallback()---------------

    public function testGetTypeCallbackParamNameWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->getTypeCallback(0);
    }

    public function testGetTypeCallbackParamNameEmpty()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentValueException::class);
        $lp->getTypeCallback('');
    }

    public function testGetTypeCallbackUnregisteredType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(PermissionTypeNotRegisteredException::class);
        $lp->getTypeCallback('test');
    }

    public function testGetTypeCallback()
    {
        $lp = new LogicalPermissions();
        $callback = function () {
        };
        $lp->addType('test', $callback);
        $this->assertSame($lp->getTypeCallback('test'), $callback);
    }

    // ------------LogicalPermissions::setTypeCallback()---------------

    public function testSetTypeCallbackParamNameWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->setTypeCallback(0, function () {
        });
    }

    public function testSetTypeCallbackParamNameEmpty()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentValueException::class);
        $lp->setTypeCallback('', function () {
        });
    }

    public function testSetTypeCallbackUnregisteredType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(PermissionTypeNotRegisteredException::class);
        $lp->setTypeCallback('test', function () {
        });
    }

    public function testSetTypeCallbackParamCallbackWrongType()
    {
        $lp = new LogicalPermissions();
        $lp->addType('test', function () {
        });

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->setTypeCallback('test', 0);
    }

    public function testSetTypeCallback()
    {
        $lp = new LogicalPermissions();
        $lp->addType('test', function () {
        });
        $callback = function () {
        };
        $this->assertNotSame($lp->getTypeCallback('test'), $callback);
        $lp->setTypeCallback('test', $callback);
        $this->assertSame($lp->getTypeCallback('test'), $callback);
    }

    // ------------LogicalPermissions::getTypes()---------------

    public function testGetTypes()
    {
        $lp = new LogicalPermissions();
        $this->assertEquals($lp->getTypes(), []);
        $callback = function () {
        };
        $lp->addType('test', $callback);
        $types = $lp->getTypes();
        $this->assertEquals($types, ['test' => $callback]);
        $this->assertSame($types['test'], $callback);
    }

    // ------------LogicalPermissions::setTypes()---------------

    public function testSetTypesParamTypesWrongType()
    {
        $lp = new LogicalPermissions();
        $types = 55;

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->setTypes($types);
    }

    public function testSetTypesParamTypesNameWrongType()
    {
        $lp = new LogicalPermissions();
        $types = [function () {
        }];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->setTypes($types);
    }

    public function testSetTypesParamTypesNameEmpty()
    {
        $lp = new LogicalPermissions();
        $types = ['' => function () {
        }];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->setTypes($types);
    }

    public function testSetTypesParamTypesNameIsCoreKey()
    {
        $lp = new LogicalPermissions();
        $types = ['no_bypass' => function () {
        }];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->setTypes($types);
    }

    public function testSetTypesParamTypesCallbackWrongType()
    {
        $lp = new LogicalPermissions();
        $types = ['test' => 'hej'];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->setTypes($types);
    }

    public function testSetTypes()
    {
        $lp = new LogicalPermissions();
        $callback = function () {
        };
        $lp->setTypes(['test' => $callback]);
        $types = $lp->getTypes();
        $this->assertEquals($types, ['test' => $callback]);
        $this->assertSame($types['test'], $callback);
    }

    // ------------LogicalPermissions::getBypassCallback()---------------

    public function testGetBypassCallback()
    {
        $lp = new LogicalPermissions();
        $this->assertNull($lp->getBypassCallback());
    }

    // ------------LogicalPermissions::setBypassCallback()---------------

    public function testSetBypassCallbackParamCallbackWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->setBypassCallback('test');
    }

    public function testSetBypassCallback()
    {
        $lp = new LogicalPermissions();
        $callback = function () {
        };
        $lp->setBypassCallback($callback);
        $this->assertSame($lp->getBypassCallback(), $callback);
    }

    // ------------LogicalPermissions::getValidPermissionKeys()---------------

    public function testGetValidPermissionKeys()
    {
        $lp = new LogicalPermissions();
        $this->assertEquals($lp->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE']);
        $types = [
      'flag' => function ($flag, $context) {
          $access = false;
          if ($flag === 'testflag') {
              $access = !empty($context['user']['testflag']);
          }

          return $access;
      },
      'role' => function ($role, $context) {
          $access = false;
          if (!empty($context['user']['roles'])) {
              $access = in_array($role, $context['user']['roles']);
          }

          return $access;
      },
      'misc' => function ($item, $context) {
          $access = false;
          $access = !empty($context['user'][$item]);

          return $access;
      },
    ];
        $lp->setTypes($types);
        $this->assertEquals($lp->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE', 'flag', 'role', 'misc']);
    }

    // ------------LogicalPermissions::checkAccess()---------------

    public function testCheckAccessParamPermissionsWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->checkAccess(0, []);
    }

    public function testCheckAccessParamPermissionsWrongPermissionType()
    {
        $lp = new LogicalPermissions();
        $lp->addType('flag', function () {
        });
        $permissions = [
          'flag' => 50,
        ];

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessParamPermissionsNestedTypes()
    {
        $lp = new LogicalPermissions();
        $lp->addType('flag', function () {
        });

        //Directly nested
        $permissions = [
          'flag' => [
            'flag' => 'testflag',
          ],
        ];

        $caught = false;
        try {
            $lp->checkAccess($permissions);
        } catch (Exception $e) {
            $this->assertEquals(get_class($e), 'Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException');
            $caught = true;
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

        $caught = false;
        try {
            $lp->checkAccess($permissions);
        } catch (Exception $e) {
            $this->assertEquals(get_class($e), 'Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException');
            $caught = true;
        }
        $this->assertTrue($caught);
    }

    public function testCheckAccessParamPermissionsUnregisteredType()
    {
        $lp = new LogicalPermissions();

        $permissions = [
          'flag' => 'testflag',
        ];

        $this->expectException(PermissionTypeNotRegisteredException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessParamContextWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->checkAccess(false, 0);
    }

    public function testCheckAccessBypassAccessCheckContextPassingArray()
    {
        $lp = new LogicalPermissions();
        $user = ['id' => 1];
        $bypass_callback = function ($context) use ($user) {
            $this->assertTrue(isset($context['user']));
            $this->assertEquals($context['user'], $user);

            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $lp->checkAccess(false, ['user' => $user]);
    }

    public function testCheckAccessBypassAccessCheckContextPassingObject()
    {
        $lp = new LogicalPermissions();
        $user = ['id' => 1];
        $bypass_callback = function ($context) use ($user) {
            $this->assertTrue(isset($context->user));
            $this->assertEquals($context->user, $user);

            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $context = new stdClass();
        $context->user = $user;
        $lp->checkAccess(false, $context);
    }

    public function testCheckAccessParamAllowBypassWrongType()
    {
        $lp = new LogicalPermissions();

        $this->expectException(InvalidArgumentTypeException::class);
        $lp->checkAccess(false, [], 'test');
    }

    public function testCheckAccessEmptyArrayAllow()
    {
        $lp = new LogicalPermissions();
        $this->assertTrue($lp->checkAccess([]));
    }

    public function testCheckAccessBypassAccessWrongReturnType()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return 1;
        };
        $lp->setBypassCallback($bypass_callback);

        $this->expectException(InvalidCallbackReturnTypeException::class);
        $lp->checkAccess(false);
    }

    public function testCheckAccessBypassAccessIllegalDescendant()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'OR' => [
            'no_bypass' => true,
          ],
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessBypassAccessAllow()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $this->assertTrue($lp->checkAccess(false));
    }

    public function testCheckAccessBypassAccessDeny()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return false;
        };
        $lp->setBypassCallback($bypass_callback);
        $this->assertFalse($lp->checkAccess(false));
    }

    public function testCheckAccessBypassAccessDeny2()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $this->assertFalse($lp->checkAccess(false, [], false));
    }

    public function testCheckAccessNoBypassWrongType()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return true;
        };
        $lp->setBypassCallback($bypass_callback);

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess(['no_bypass' => 'test']);
    }

    public function testCheckAccessNoBypassEmptyPermissionsAllow()
    {
        $lp = new LogicalPermissions();
        $this->assertTrue($lp->checkAccess(['no_bypass' => true]));
    }

    public function testCheckAccessNoBypassAccessBooleanAllow()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $permissions = ['no_bypass' => false];
        $this->assertTrue($lp->checkAccess($permissions));
        //Test that permission array is not changed
        $this->assertTrue(isset($permissions['no_bypass']));
    }

    public function testCheckAccessNoBypassAccessBooleanDeny()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $this->assertFalse($lp->checkAccess(['no_bypass' => true, false], []));
    }

    public function testCheckAccessNoBypassAccessStringAllow()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $permissions = ['no_bypass' => 'False'];
        $this->assertTrue($lp->checkAccess($permissions));
        //Test that permission array is not changed
        $this->assertTrue(isset($permissions['no_bypass']));
    }

    public function testCheckAccessNoBypassAccessStringDeny()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) {
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $this->assertFalse($lp->checkAccess(['no_bypass' => 'True', false], []));
    }

    public function testCheckAccessNoBypassAccessArrayAllow()
    {
        $lp = new LogicalPermissions();
        $types = [
      'flag' => function ($flag, $context) {
          $access = false;
          if ($flag === 'never_bypass') {
              $access = !empty($context['user']['never_bypass']);
          }

          return $access;
      },
    ];
        $lp->setTypes($types);
        $bypass_callback = function ($context) { //Simulates for example that the user is a superuser with ability to bypass access
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $permissions = [
      'no_bypass' => [
        'flag' => 'never_bypass',
      ],
    ];
        $user = [
      'id'           => 1,
      'never_bypass' => false,
    ];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessNoBypassAccessArrayDeny()
    {
        $lp = new LogicalPermissions();
        $types = [
          'flag' => function ($flag, $context) {
              $access = false;
              if ($flag === 'never_bypass') {
                  $access = !empty($context['user']['never_bypass']);
              }

              return $access;
          },
        ];
        $lp->setTypes($types);
        $bypass_callback = function ($context) { //Simulates for example that the user is a superuser with ability to bypass access
            return true;
        };
        $lp->setBypassCallback($bypass_callback);
        $permissions = [
          'no_bypass' => [
            'flag' => 'never_bypass',
          ],
          false,
        ];
        $user = [
          'id'           => 1,
          'never_bypass' => true,
        ];
        $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessWrongPermissionCallbackReturnType()
    {
        $lp = new LogicalPermissions();
        $types = [
          'flag' => function ($flag, $context) {
              $access = false;
              if ($flag === 'testflag') {
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
          'id'       => 1,
          'testflag' => true,
        ];

        $this->expectException(InvalidCallbackReturnTypeException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessSingleItemAllow()
    {
        $lp = new LogicalPermissions();
        $types = [
          'flag' => function ($flag, $context) {
              $access = false;
              if ($flag === 'testflag') {
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
          'id'       => 1,
          'testflag' => true,
        ];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessSingleItemDeny()
    {
        $lp = new LogicalPermissions();
        $types = [
          'flag' => function ($flag, $context) {
              $access = false;
              if ($flag === 'testflag') {
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

    public function testCheckAccessMultipleTypesShorthandOR()
    {
        $lp = new LogicalPermissions();
        $types = [
          'flag' => function ($flag, $context) {
              $access = false;
              if ($flag === 'testflag') {
                  $access = !empty($context['user']['testflag']);
              }

              return $access;
          },
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
                  $access = in_array($role, $context['user']['roles']);
              }

              return $access;
          },
          'misc' => function ($item, $context) {
              $access = false;
              $access = !empty($context['user'][$item]);

              return $access;
          },
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
        $user['test'] = true;
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        //0 1 0
        $user['test'] = false;
        $user['roles'] = ['admin'];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        //0 1 1
        $user['test'] = true;
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        //1 0 0
        $user = [
          'id'       => 1,
          'testflag' => true,
        ];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        //1 0 1
        $user['test'] = true;
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        //1 1 0
        $user['test'] = false;
        $user['roles'] = ['admin'];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        //1 1 1
        $user['test'] = true;
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessMultipleItemsShorthandOR()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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

    public function testCheckAccessANDWrongValueType()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessANDTooFewElements()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessMultipleItemsAND()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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

    public function testCheckAccessNANDWrongValueType()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessNANDTooFewElements()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessMultipleItemsNAND()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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

    public function testCheckAccessORWrongValueType()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessORTooFewElements()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessMultipleItemsOR()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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

    public function testCheckAccessNORWrongValueType()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessNORTooFewElements()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessMultipleItemsNOR()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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

    public function testCheckAccessXORWrongValueType()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessXORTooFewElements()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessMultipleItemsXOR()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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

    public function testCheckAccessNOTWrongValueType()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
                  $access = in_array($role, $context['user']['roles']);
              }

              return $access;
          },
        ];
        $lp->setTypes($types);
        $permissions = [
          'role' => [
            'NOT' => true,
          ],
        ];
        $user = [
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessNOTArrayTooFewElements()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessNOTStringEmpty()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin'],
        ];

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions, ['user' => $user]);
    }

    public function testCheckAccessMultipleItemsNOT()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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

        $this->expectException(InvalidValueForLogicGateException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessSingleItemNOTString()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin', 'editor'],
        ];
        $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
        unset($user['roles']);
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        $user['roles'] = ['editor'];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessSingleItemNOTArray()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
          'roles' => ['admin', 'editor'],
        ];
        $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
        unset($user['roles']);
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        $user['roles'] = ['editor'];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessBoolTRUEIllegalDescendant()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'role' => [true],
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessBoolTRUE()
    {
        $lp = new LogicalPermissions();
        $permissions = true;
        $this->assertTrue($lp->checkAccess($permissions));
    }

    public function testCheckAccessBoolTRUEArray()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          true,
        ];
        $this->assertTrue($lp->checkAccess($permissions));
    }

    public function testCheckAccessBoolFALSEIllegalDescendant()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'role' => [false],
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessBoolFALSE()
    {
        $lp = new LogicalPermissions();
        $permissions = false;
        $this->assertFalse($lp->checkAccess($permissions));
    }

    public function testCheckAccessBoolFALSEArray()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          false,
        ];
        $this->assertFalse($lp->checkAccess($permissions));
    }

    public function testCheckAccessBoolFALSEBypass()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) { //Simulates for example that the user is a superuser with ability to bypass access
            return true;
        };
        $lp->setBypassCallback($bypass_callback);

        $permissions = [
          false,
        ];
        $this->assertTrue($lp->checkAccess($permissions));
    }

    public function testCheckAccessBoolFALSENoBypass()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) { //Simulates for example that the user is a superuser with ability to bypass access
            return true;
        };
        $lp->setBypassCallback($bypass_callback);

        $permissions = [
          'no_bypass' => true,
          false,
        ];
        $this->assertFalse($lp->checkAccess($permissions));
    }

    public function testCheckAccessStringTRUEIllegalChildrenSingleValue()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'TRUE' => false,
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessStringTRUEIllegalChildrenArray()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'TRUE' => [],
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessStringTRUEIllegalDescendant()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'role' => ['TRUE'],
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessStringTRUE()
    {
        $lp = new LogicalPermissions();
        $permissions = 'TRUE';
        $this->assertTrue($lp->checkAccess($permissions));
    }

    public function testCheckAccessStringTRUEArray()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'TRUE',
        ];

        $this->assertTrue($lp->checkAccess($permissions));
    }

    public function testCheckAccessStringFALSEIllegalChildrenSingleValue()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'FALSE' => false,
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessStringFALSEIllegalChildrenArray()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'FALSE' => [],
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessStringFALSEIllegalDescendant()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'role' => ['FALSE'],
        ];

        $this->expectException(InvalidArgumentValueException::class);
        $lp->checkAccess($permissions);
    }

    public function testCheckAccessStringFALSE()
    {
        $lp = new LogicalPermissions();
        $permissions = 'FALSE';
        $this->assertFalse($lp->checkAccess($permissions));
    }

    public function testCheckAccessStringFALSEArray()
    {
        $lp = new LogicalPermissions();
        $permissions = [
          'FALSE',
        ];
        $this->assertFalse($lp->checkAccess($permissions));
    }

    public function testCheckAccessStringFALSEBypass()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) { //Simulates for example that the user is a superuser with ability to bypass access
            return true;
        };
        $lp->setBypassCallback($bypass_callback);

        $permissions = [
          'FALSE',
        ];
        $this->assertTrue($lp->checkAccess($permissions));
    }

    public function testCheckAccessStringFALSENoBypass()
    {
        $lp = new LogicalPermissions();
        $bypass_callback = function ($context) { //Simulates for example that the user is a superuser with ability to bypass access
            return true;
        };
        $lp->setBypassCallback($bypass_callback);

        $permissions = [
          'no_bypass' => true,
          'FALSE',
        ];
        $this->assertFalse($lp->checkAccess($permissions));
    }

    public function testMixedBooleans()
    {
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

    public function testCheckAccessNestedLogic()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          false,
          'FALSE',
        ];
        $user = [
          'id'    => 1,
          'roles' => ['admin', 'editor'],
        ];
        $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
        unset($user['roles']);
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        $user['roles'] = ['editor'];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessLogicGateFirst()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
            true,
            'TRUE',
          ],
        ];
        $user = [
          'id'    => 1,
          'roles' => ['admin', 'editor'],
        ];
        $this->assertFalse($lp->checkAccess($permissions, ['user' => $user]));
        unset($user['roles']);
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
        $user['roles'] = ['editor'];
        $this->assertTrue($lp->checkAccess($permissions, ['user' => $user]));
    }

    public function testCheckAccessShorthandORMixedNumericStringKeys()
    {
        $lp = new LogicalPermissions();
        $types = [
          'role' => function ($role, $context) {
              $access = false;
              if (!empty($context['user']['roles'])) {
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
          'id'    => 1,
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
