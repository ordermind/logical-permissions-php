<?php

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\PermissionTypeCollectionInterface;
use Ordermind\LogicalPermissions\PermissionTypeInterface;

/**
 * Collection of permission types.
 */
class PermissionTypeCollection implements PermissionTypeCollectionInterface {
  /**
   * @var array
   */
  protected $types = [];

  /**
   * {@inheritdoc}
   */
  public function add(PermissionTypeInterface $permissionType, $overwriteIfExists = FALSE) {
    $name = $permissionType->getName();

    if(!is_string($name)) {
      throw new InvalidPermissionTypeException(sprintf('The name of the permission type %s must be a string.', get_class($permissionType)));
    }
    if(!$name) {
      throw new InvalidPermissionTypeException(sprintf('The name of the permission type %s must not be empty.', get_class($permissionType)));
    }
    if(in_array(strtoupper($name), $reservedKeys = $this->getReservedPermissionKeys())) {
      throw new InvalidPermissionTypeException(sprintf('The permission type %s has the illegal name "%s". It must not be one of the following values: [%s]', get_class($permissionType), $name, implode(', ', $reservedKeys)));
    }
    if($this->has($name) && !$overwriteIfExists) {
      throw new PermissionTypeAlreadyExistsException("The permission type \"$name\" already exists!");
    }

    $this->types[$name] = $permissionType;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function remove($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name must not be empty.');
    }

    unset($this->types[$name]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function has($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name must not be empty.');
    }

    return isset($this->types[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name parameter must not be empty.');
    }
    if(!$this->has($name)) {
      return NULL;
    }

    return $this->types[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    return $this->types;
  }

  /**
   * @internal
   *
   * Gets reserved permission keys.
   *
   * @return array
   */
  public function getReservedPermissionKeys() {
    return ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE'];
  }
}
