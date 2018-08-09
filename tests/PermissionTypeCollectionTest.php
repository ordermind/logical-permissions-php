<?php

namespace Ordermind\LogicalPermissions\Test;

use Ordermind\LogicalPermissions\Test\LogicalPermissionsPHPUnitShim;
use Ordermind\LogicalPermissions\PermissionTypeCollection;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\ErroneousNameType;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\EmptyName;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\IllegalName;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\AlwaysAllow;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\Role;

class PermissionTypeCollectionTest extends LogicalPermissionsPHPUnitShim {

  /*-----------PermissionTypeCollection::add()-------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException
   */
  public function testAddWrongNameType() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new ErroneousNameType());
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException
   */
  public function testAddEmptyName() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new EmptyName());
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException
   */
  public function testAddIllegalName() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new IllegalName());
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException
   */
  public function testAddTypeAlreadyExists() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new AlwaysAllow());
    $permissionTypeCollection->add(new AlwaysAllow());
  }

  public function testAdd() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new AlwaysAllow());
    $this->assertTrue($permissionTypeCollection->has('always_allow'));
  }

  public function testAddUpdate() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new AlwaysAllow());
    $permissionTypeCollection->add(new AlwaysAllow(), true);
  }

  /*-------------PermissionTypeCollection::remove()--------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testRemoveWrongNameType() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->remove(0);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testRemoveEmptyName() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->remove('');
  }

  public function testRemoveUnregisteredType() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->remove('test');
  }

  public function testRemove() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new AlwaysAllow());
    $permissionTypeCollection->remove('always_allow');
    $this->assertFalse($permissionTypeCollection->has('always_allow'));
  }

  /*------------PermissionTypeCollection::has()---------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testHasWrongNameType() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->has(0);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testHasEmptyName() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->has('');
  }

  public function testHas() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $this->assertFalse($permissionTypeCollection->has('always_allow'));
    $permissionTypeCollection->add(new AlwaysAllow());
    $this->assertTrue($permissionTypeCollection->has('always_allow'));
  }

  /*------------PermissionTypeCollection::get()---------------*/

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException
   */
  public function testGetWrongNameType() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->get(0);
  }

  /**
   * @expectedException Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException
   */
  public function testGetEmptyName() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->get('');
  }

  public function testGetUnregisteredType() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $this->assertNull($permissionTypeCollection->get('unregistered'));
  }

  public function testGet() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionType = new AlwaysAllow();
    $permissionTypeCollection->add($permissionType);
    $this->assertSame($permissionTypeCollection->get('always_allow'), $permissionType);
  }

  /*------------PermissionTypeCollection::toArray()---------------*/

  public function testToArray() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $this->assertEquals($permissionTypeCollection->toArray(), []);
    $permissionType = new AlwaysAllow();
    $permissionTypeCollection->add($permissionType);
    $types = $permissionTypeCollection->toArray();
    $this->assertEquals($types, ['always_allow' => $permissionType]);
    $this->assertSame($types['always_allow'], $permissionType);
  }

  /*------------PermissionTypeCollection::getValidPermissionKeys()---------------*/

  public function testGetValidPermissionKeys() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $this->assertEquals($permissionTypeCollection->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE']);
    $permissionTypeCollection->add(new AlwaysAllow());
    $this->assertEquals($permissionTypeCollection->getValidPermissionKeys(), ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE', 'always_allow']);
  }
}
