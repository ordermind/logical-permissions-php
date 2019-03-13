<?php

namespace Ordermind\LogicalPermissions\Test;

use Ordermind\LogicalPermissions\AccessChecker;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidReturnTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\PermissionTypeCollection;
use Ordermind\LogicalPermissions\Test\LogicalPermissionsPHPUnitShim;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\InvalidReturnType as PermissionTypeInvalidReturnType;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\AlwaysAllow as PermissionTypeAlwaysAllow;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\Flag as PermissionTypeFlag;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\Role as PermissionTypeRole;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\Misc as PermissionTypeMisc;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\InvalidReturnType as BypassCheckerInvalidReturnType;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\AlwaysAllow as BypassCheckerAlwaysAllow;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\AlwaysDeny as BypassCheckerAlwaysDeny;

class AccessCheckerTest extends LogicalPermissionsPHPUnitShim {

  /*------------AccessChecker::setPermissionTypeCollection()---------------*/
  /*------------AccessChecker::getPermissionTypeCollection()---------------*/

  public function testSetGetPermissionTypeCollection() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = new PermissionTypeCollection();
    $accessChecker->setPermissionTypeCollection($permissionTypeCollection);
    $this->assertSame($accessChecker->getPermissionTypeCollection(), $permissionTypeCollection);
  }

  /*------------AccessChecker::setBypassAccessChecker()---------------*/
  /*------------AccessChecker::getBypassAccessChecker()---------------*/

  public function testSetGetBypassAccessChecker() {
    $accessChecker = new AccessChecker();
    $bypassAccessChecker = new BypassCheckerAlwaysAllow();
    $accessChecker->setBypassAccessChecker($bypassAccessChecker);
    $this->assertSame($accessChecker->getBypassAccessChecker(), $bypassAccessChecker);
  }

  /*------------AccessChecker::getValidPermissionKeys()---------------*/

  public function testGetValidPermissionKeys() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $this->assertEquals($accessChecker->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE']);
    $permissionTypeCollection->add(new PermissionTypeAlwaysAllow());
    $this->assertEquals($accessChecker->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE', 'always_allow']);
  }

  /*------------AccessChecker::checkAccess()---------------*/

  public function testCheckAccessParamPermissionsWrongType() {
    $this->expectException(InvalidArgumentTypeException::class);

    $accessChecker = new AccessChecker();
    $accessChecker->checkAccess(0, []);
  }

  public function testCheckAccessParamPermissionsWrongPermissionType() {
    $this->expectException(InvalidArgumentTypeException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeFlag());
    $permissions = [
      'flag' => 50,
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessParamPermissionsNestedTypes() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeFlag());

    //Directly nested
    $permissions = [
      'flag' => [
        'flag' => 'testflag',
      ],
    ];

    $caught = FALSE;
    try {
      $accessChecker->checkAccess($permissions);
    }
    catch(\Exception $e) {
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
      $accessChecker->checkAccess($permissions);
    }
    catch(\Exception $e) {
      $this->assertEquals(get_class($e), 'Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException');
      $caught = TRUE;
    }
    $this->assertTrue($caught);
  }

  public function testCheckAccessParamPermissionsUnregisteredType() {
    $this->expectException(PermissionTypeNotRegisteredException::class);

    $accessChecker = new AccessChecker();

    $permissions = [
      'flag' => 'testflag',
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessParamContextWrongType() {
    $this->expectException(InvalidArgumentTypeException::class);

    $accessChecker = new AccessChecker();
    $accessChecker->checkAccess(FALSE, 0);
  }

// ----- Anonymous classes are only available in PHP >=7 so I'm disabling these tests for now. ----//
//   public function testCheckAccessBypassAccessCheckContextPassingArray() {
//     $accessChecker = new AccessChecker();
//
//     $bypassAccessChecker = new class extends AccessCheckerTest implements BypassAccessCheckerInterface {
//       public function checkBypassAccess($context) {
//         $this->assertTrue(isset($context['user']['id']));
//         $this->assertEquals($context['user']['id'], 1);
//
//         return TRUE;
//       }
//     };
//     $accessChecker->setBypassAccessChecker($bypassAccessChecker);
//
//     $user = ['id' => 1];
//     $accessChecker->checkAccess(FALSE, ['user' => $user]);
//   }
//
//   public function testCheckAccessBypassAccessCheckContextPassingObject() {
//     $accessChecker = new AccessChecker();
//
//     $bypassAccessChecker = new class extends AccessCheckerTest implements BypassAccessCheckerInterface {
//       public function checkBypassAccess($context) {
//         $this->assertTrue(isset($context->user->id));
//         $this->assertEquals($context->user->id, 1);
//
//         return TRUE;
//       }
//     };
//
//     $accessChecker->setBypassAccessChecker($bypassAccessChecker);
//     $context = new stdClass();
//     $user = ['id' => 1];
//     $context->user = $user;
//     $accessChecker->checkAccess(FALSE, $context);
//   }
//----------------------//

  public function testCheckAccessParamAllowBypassWrongType() {
    $this->expectException(InvalidArgumentTypeException::class);

    $accessChecker = new AccessChecker();
    $accessChecker->checkAccess(FALSE, [], 'test');
  }

  public function testCheckAccessEmptyArrayAllow() {
    $accessChecker = new AccessChecker();
    $this->assertTrue($accessChecker->checkAccess([]));
  }

  public function testCheckAccessBypassAccessWrongReturnType() {
    $this->expectException(InvalidReturnTypeException::class);

    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerInvalidReturnType());
    $accessChecker->checkAccess(FALSE);
  }

  public function testCheckAccessBypassAccessIllegalDescendant() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissions = [
      'OR' => [
        'no_bypass' => true,
      ],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessBypassAccessAllow() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow());
    $this->assertTrue($accessChecker->checkAccess(FALSE));
  }

  public function testCheckAccessBypassAccessDeny() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysDeny());
    $this->assertFalse($accessChecker->checkAccess(FALSE));
  }

  public function testCheckAccessBypassAccessDeny2() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysDeny());
    $this->assertFalse($accessChecker->checkAccess(FALSE, [], FALSE));
  }

  public function testCheckAccessNoBypassWrongType() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow());
    $accessChecker->checkAccess(['no_bypass' => 'test']);
  }

  public function testCheckAccessNoBypassEmptyPermissionsAllow() {
    $accessChecker = new AccessChecker();
    $this->assertTrue($accessChecker->checkAccess(['no_bypass' => TRUE]));
  }

  public function testCheckAccessNoBypassAccessBooleanAllow() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow());
    $permissions = ['no_bypass' => FALSE];
    $this->assertTrue($accessChecker->checkAccess($permissions));
    //Test that permission array is not changed
    $this->assertTrue(isset($permissions['no_bypass']));
  }

  public function testCheckAccessNoBypassAccessBooleanDeny() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow());
    $this->assertFalse($accessChecker->checkAccess(['no_bypass' => TRUE, FALSE], []));
  }

  public function testCheckAccessNoBypassAccessStringAllow() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow());
    $permissions = ['no_bypass' => 'False'];
    $this->assertTrue($accessChecker->checkAccess($permissions));
    //Test that permission array is not changed
    $this->assertTrue(isset($permissions['no_bypass']));
  }

  public function testCheckAccessNoBypassAccessStringDeny() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow());
    $this->assertFalse($accessChecker->checkAccess(['no_bypass' => 'True', FALSE], []));
  }

  public function testCheckAccessNoBypassAccessArrayAllow() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeFlag());
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow()); //Simulates for example that the user is a superuser with ability to bypass access
    $permissions = [
      'no_bypass' => [
        'flag' => 'never_bypass',
      ],
    ];
    $user = [
      'id' => 1,
      'never_bypass' => FALSE,
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user]));
  }

  public function testCheckAccessNoBypassAccessArrayDeny() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeFlag());
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow()); //Simulates for example that the user is a superuser with ability to bypass access
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
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user]));
  }

  public function testCheckAccessWrongPermissionTypeReturnType() {
    $this->expectException(InvalidReturnTypeException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeInvalidReturnType());
    $permissions = [
      'no_bypass' => [
        'invalid_return_type' => 'never_bypass',
      ],
      'invalid_return_type' => 'test',
    ];
    $user = [
      'id' => 1,
      'test' => TRUE,
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user]);
  }

  public function testCheckAccessSingleItemAllow() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeFlag());
    $permissions = [
      'flag' => 'testflag',
    ];
    $user = [
      'id' => 1,
      'testflag' => TRUE,
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessSingleItemDeny() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeFlag());
    $permissions = [
      'flag' => 'testflag',
    ];
    $user = [
      'id' => 1,
    ];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessMultipleTypesShorthandOR() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeFlag());
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissionTypeCollection->add(new PermissionTypeMisc());
    $permissions = [
      'flag' => 'testflag',
      'role' => 'admin',
      'misc' => 'test',
    ];
    $user = [
      'id' => 1,
    ];
    //OR truth table
    //0 0 0
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 0 1
    $user['test'] = TRUE;
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 0
    $user['test'] = FALSE;
    $user['roles'] = ['admin'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 1
    $user['test'] = TRUE;
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 0
    $user = [
      'id' => 1,
      'testflag' => TRUE,
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 1
    $user['test'] = TRUE;
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 0
    $user['test'] = FALSE;
    $user['roles'] = ['admin'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 1
    $user['test'] = TRUE;
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessMultipleItemsShorthandOR() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => ['admin', 'editor'],
    ];
    $user = [
      'id' => 1,
    ];
    //OR truth table
    //0 0
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = [];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0
    $user['roles'] = ['admin'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1
    $user['roles'] = ['editor', 'admin'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessANDWrongValueType() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'AND' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessANDTooFewElements() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'AND' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessMultipleItemsAND() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = [];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessNANDWrongValueType() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NAND' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessNANDTooFewElements() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NAND' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessMultipleItemsNAND() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = [];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessORWrongValueType() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'OR' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessORTooFewElements() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'OR' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessMultipleItemsOR() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = [];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessNORWrongValueType() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NOR' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessNORTooFewElements() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NOR' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessMultipleItemsNOR() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = [];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessXORWrongValueType() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'XOR' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessXORTooFewElements() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'XOR' => ['admin'],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessMultipleItemsXOR() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = [];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 0 1
    $user['roles'] = ['writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 0
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //0 1 1
    $user['roles'] = ['editor', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 0
    $user['roles'] = ['admin'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 0 1
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 0
    $user['roles'] = ['admin', 'editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    //1 1 1
    $user['roles'] = ['admin', 'editor', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessNOTWrongValueType() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NOT' => TRUE,
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessNOTArrayTooFewElements() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NOT' => [],
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessNOTStringEmpty() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NOT' => '',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin'],
    ];
    $accessChecker->checkAccess($permissions, ['user' => $user], FALSE);
  }

  public function testCheckAccessMultipleItemsNOT() {
    $this->expectException(InvalidValueForLogicGateException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NOT' => [
          'admin',
          'editor',
          'writer',
        ],
      ],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessSingleItemNOTString() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [
        'NOT' => 'admin',
      ],
    ];
    $user = [
      'id' => 1,
      'roles' => ['admin', 'editor'],
    ];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    unset($user['roles']);
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessSingleItemNOTArray() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    unset($user['roles']);
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessBoolTRUEIllegalDescendant() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [TRUE],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessBoolTRUE() {
    $accessChecker = new AccessChecker();
    $permissions = TRUE;
    $this->assertTrue($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessBoolTRUEArray() {
    $accessChecker = new AccessChecker();
    $permissions = [
      TRUE,
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessBoolFALSEIllegalDescendant() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => [FALSE],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessBoolFALSE() {
    $accessChecker = new AccessChecker();
    $permissions = FALSE;
    $this->assertFalse($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessBoolFALSEArray() {
    $accessChecker = new AccessChecker();
    $permissions = [
      FALSE,
    ];
    $this->assertFalse($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessBoolFALSEBypass() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow()); //Simulates for example that the user is a superuser with ability to bypass access
    $permissions = [
      FALSE,
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessBoolFALSENoBypass() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow()); //Simulates for example that the user is a superuser with ability to bypass access
    $permissions = [
      'no_bypass' => TRUE,
      FALSE,
    ];
    $this->assertFalse($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessStringTRUEIllegalChildrenSingleValue() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissions = [
      'TRUE' => FALSE,
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessStringTRUEIllegalChildrenArray() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissions = [
      'TRUE' => [],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessStringTRUEIllegalDescendant() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => ['TRUE'],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessStringTRUE() {
    $accessChecker = new AccessChecker();
    $permissions = 'TRUE';
    $this->assertTrue($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessStringTRUEArray() {
    $accessChecker = new AccessChecker();
    $permissions = [
      'TRUE',
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessStringFALSEIllegalChildrenSingleValue() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissions = [
      'FALSE' => FALSE,
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessStringFALSEIllegalChildrenArray() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissions = [
      'FALSE' => [],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessStringFALSEIllegalDescendant() {
    $this->expectException(InvalidArgumentValueException::class);

    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
    $permissions = [
      'role' => ['FALSE'],
    ];
    $accessChecker->checkAccess($permissions);
  }

  public function testCheckAccessStringFALSE() {
    $accessChecker = new AccessChecker();
    $permissions = 'FALSE';
    $this->assertFalse($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessStringFALSEArray() {
    $accessChecker = new AccessChecker();
    $permissions = [
      'FALSE',
    ];
    $this->assertFalse($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessStringFALSEBypass() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow()); //Simulates for example that the user is a superuser with ability to bypass access

    $permissions = [
      'FALSE',
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessStringFALSENoBypass() {
    $accessChecker = new AccessChecker();
    $accessChecker->setBypassAccessChecker(new BypassCheckerAlwaysAllow()); //Simulates for example that the user is a superuser with ability to bypass access

    $permissions = [
      'no_bypass' => TRUE,
      'FALSE',
    ];
    $this->assertFalse($accessChecker->checkAccess($permissions));
  }
  public function testMixedBooleans() {
    $accessChecker = new AccessChecker();

    $permissions = [
      'FALSE',
      'TRUE',
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions));

    $permissions = [
      'OR' => [
        'FALSE',
        'TRUE',
      ],
    ];
    $this->assertTrue($accessChecker->checkAccess($permissions));

    $permissions = [
      'AND' => [
        'TRUE',
        'FALSE',
      ],
    ];
    $this->assertFalse($accessChecker->checkAccess($permissions));
  }

  public function testCheckAccessNestedLogic() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    unset($user['roles']);
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessLogicGateFirst() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    unset($user['roles']);
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }

  public function testCheckAccessShorthandORMixedNumericStringKeys() {
    $accessChecker = new AccessChecker();
    $permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
    $permissionTypeCollection->add(new PermissionTypeRole());
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
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    unset($user['roles']);
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor', 'writer'];
    $this->assertFalse($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor', 'writer', 'role1'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['editor', 'writer', 'role2'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
    $user['roles'] = ['admin', 'writer'];
    $this->assertTrue($accessChecker->checkAccess($permissions, ['user' => $user], FALSE));
  }
}
