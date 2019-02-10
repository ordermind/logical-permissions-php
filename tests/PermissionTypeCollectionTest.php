<?php

namespace Ordermind\LogicalPermissions\Test;

use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Test\LogicalPermissionsPHPUnitShim;
use Ordermind\LogicalPermissions\PermissionTypeCollection;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\ErroneousNameType;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\EmptyName;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\IllegalName;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\AlwaysAllow;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionType\Role;

class PermissionTypeCollectionTest extends LogicalPermissionsPHPUnitShim {

  /*-----------PermissionTypeCollection::add()-------------*/

  public function testAddWrongNameType() {
    $this->expectException(InvalidPermissionTypeException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new ErroneousNameType());
  }

  public function testAddEmptyName() {
    $this->expectException(InvalidPermissionTypeException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new EmptyName());
  }

  public function testAddIllegalName() {
    $this->expectException(InvalidPermissionTypeException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new IllegalName());
  }

  public function testAddTypeAlreadyExists() {
    $this->expectException(PermissionTypeAlreadyExistsException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new AlwaysAllow());
    $permissionTypeCollection->add(new AlwaysAllow());
  }

  public function testAdd() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new AlwaysAllow());
    $this->assertTrue($permissionTypeCollection->has('always_allow'));
  }

  /**
   * @doesNotPerformAssertions
   */
  public function testAddUpdate() {
    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->add(new AlwaysAllow());
    $permissionTypeCollection->add(new AlwaysAllow(), true);
  }

  /*-------------PermissionTypeCollection::remove()--------------*/

  public function testRemoveWrongNameType() {
    $this->expectException(InvalidArgumentTypeException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->remove(0);
  }

  public function testRemoveEmptyName() {
    $this->expectException(InvalidArgumentValueException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->remove('');
  }

  /**
   * @doesNotPerformAssertions
   */
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

  public function testHasWrongNameType() {
    $this->expectException(InvalidArgumentTypeException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->has(0);
  }

  public function testHasEmptyName() {
    $this->expectException(InvalidArgumentValueException::class);

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

  public function testGetWrongNameType() {
    $this->expectException(InvalidArgumentTypeException::class);

    $permissionTypeCollection = new PermissionTypeCollection();
    $permissionTypeCollection->get(0);
  }

  public function testGetEmptyName() {
    $this->expectException(InvalidArgumentValueException::class);

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
}
