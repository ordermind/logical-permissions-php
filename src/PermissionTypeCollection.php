<?php

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\PermissionTypeCollectionInterface;
use Ordermind\LogicalPermissions\PermissionTypeInterface;

class PermissionTypeCollection implements PermissionTypeCollectionInterface {
  /**
   * @var array
   */
  protected $types = [];

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    return $this->types;
  }

  /**
   * {@inheritdoc}
   */
  public function add(PermissionTypeInterface $permissionType, $overwriteIfExists = FALSE) {
    $name = $permissionType->getName();

    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name cannot be empty.');
    }
    if(in_array(strtoupper($name), $core_keys = $this->getCorePermissionKeys())) {
      throw new InvalidArgumentValueException("The name has the illegal value \"$name\". It cannot be one of the following values: " . implode(',', $core_keys));
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
      throw new InvalidArgumentValueException('The name cannot be empty.');
    }

    unset($this->types[$name]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name parameter cannot be empty.');
    }
    if(!$this->has($name)) {
      return NULL;
    }

    return $this->types[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function has($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name cannot be empty.');
    }

    return isset($this->types[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function getValidPermissionKeys() {
    return array_merge($this->getCorePermissionKeys(), array_keys($this->types));
  }

  protected function getCorePermissionKeys() {
    return ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE'];
  }
}
