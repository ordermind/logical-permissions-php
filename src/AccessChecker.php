<?php

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\Exceptions\InvalidReturnTypeException;
use Ordermind\LogicalPermissions\PermissionTypeCollection;
use Ordermind\LogicalPermissions\PermissionTypeCollectionInterface;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\AccessCheckerInterface;

/**
 * Checks access based on registered permission types, a permission tree and a context.
 */
class AccessChecker implements AccessCheckerInterface {
  /**
   * @var Ordermind\LogicalPermissions\PermissionTypeCollectionInterface
   */
  protected $permissionTypeCollection;

  /**
   * @var Ordermind\LogicalPermissions\BypassAccessCheckerInterface
   */
  protected $bypassAccessChecker;

  /**
   * @internal
   */
  public function __construct() {
    $this->setPermissionTypeCollection(new PermissionTypeCollection());
  }

  /**
   * {@inheritdoc}
   */
  public function setPermissionTypeCollection(PermissionTypeCollectionInterface $permissionTypeCollection) {
    $this->permissionTypeCollection = $permissionTypeCollection;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionTypeCollection() {
    return $this->permissionTypeCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function setBypassAccessChecker(BypassAccessCheckerInterface $bypassAccessChecker) {
    $this->bypassAccessChecker = $bypassAccessChecker;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBypassAccessChecker() {
    return $this->bypassAccessChecker;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidPermissionKeys() {
    return array_merge($this->getPermissionTypeCollection()->getReservedPermissionKeys(), array_keys($this->getPermissionTypeCollection()->toArray()));
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess($permissions, $context = NULL, $allowBypass = TRUE) {
    if(!is_array($permissions) && !is_string($permissions) && !is_bool($permissions)) {
      throw new InvalidArgumentTypeException('The permissions parameter must be an array or in certain cases a string or boolean.');
    }
    if(!is_null($context) && !is_array($context) && !is_object($context)) {
      throw new InvalidArgumentTypeException('The context parameter must be an array or object.');
    }
    if(!is_bool($allowBypass)) {
      throw new InvalidArgumentTypeException('The allow_bypass parameter must be a boolean.');
    }

    if(is_array($permissions)) {
      // uppercasing of no_bypass key for backward compatibility
      if(is_array($permissions) && array_key_exists('no_bypass', $permissions)) {
        $permissions['NO_BYPASS'] = $permissions['no_bypass'];
        unset($permissions['no_bypass']);
      }

      $allowBypass = $this->checkAllowBypass($permissions, $context, $allowBypass);

      unset($permissions['NO_BYPASS']);
    }

    if($allowBypass && $this->checkBypassAccess($context)) {
      return TRUE;
    }

    if(is_array($permissions) && count($permissions)) {
      return $this->processOR($permissions, NULL, $context);
    }

    return $this->dispatch($permissions);
  }

  /**
   * @internal
   *
   * @param array $permissions
   * @param array|object|NULL $context
   * @param bool $allowBypass
   */
  protected function checkAllowBypass(array $permissions, $context, $allowBypass) {
    if(!$allowBypass) {
      return $allowBypass;
    }

    if(!array_key_exists('NO_BYPASS', $permissions)) {
      return $allowBypass;
    }

    if(is_bool($permissions['NO_BYPASS'])) {
      return !$permissions['NO_BYPASS'];
    }

    if(is_string($permissions['NO_BYPASS'])) {
      $noBypassUpper = strtoupper($permissions['NO_BYPASS']);
      if(!in_array($noBypassUpper, array('TRUE', 'FALSE'))) {
        throw new InvalidArgumentValueException('The NO_BYPASS value must be a boolean, a boolean string or an array. Current value: ' . print_r($permissions['NO_BYPASS'], TRUE));
      }

      if('TRUE' === $noBypassUpper) {
        return FALSE;
      }
      if('FALSE' === $noBypassUpper) {
        return TRUE;
      }
    }

    if(is_array($permissions['NO_BYPASS'])) {
      return !$this->processOR($permissions['NO_BYPASS'], NULL, $context);
    }

    throw new InvalidArgumentValueException('The NO_BYPASS value must be a boolean, a boolean string or an array. Current value: ' . print_r($permissions['NO_BYPASS'], TRUE));
  }

  /**
   * @internal
   *
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function checkBypassAccess($context) {
    $bypassAccessChecker = $this->getBypassAccessChecker();
    if(is_null($bypassAccessChecker)) {
      return FALSE;
    }

    $bypassAccess = $bypassAccessChecker->checkBypassAccess($context);
    if(!is_bool($bypassAccess)) {
      throw new InvalidReturnTypeException('The bypass access checker must return a boolean, see Ordermind\LogicalPermissions\BypassAccessCheckerInterface::checkBypassAccess().');
    }

    return $bypassAccess;
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function dispatch($permissions, $type = NULL, $context = NULL) {
    if(is_bool($permissions)) {
      return $this->dispatchBoolean($permissions, $type, $context);
    }

    if(is_string($permissions)) {
      return $this->dispatchString($permissions, $type, $context);
    }

    if(is_array($permissions)) {
      return $this->dispatchArray($permissions, $type, $context);
    }

    throw new InvalidArgumentTypeException("A permission must either be a boolean, a string or an array. Evaluated permissions: " . print_r($permissions, TRUE));
  }

  /**
   * @internal
   *
   * @param bool $permissions
   * @param string|NULL $type
   *
   * @return bool
   */
  protected function dispatchBoolean($permissions, $type = NULL) {
    if(!is_null($type)) {
      throw new InvalidArgumentValueException("You cannot put a boolean permission as a descendant to a permission type. Existing type: \"$type\". Evaluated permissions: " . print_r($permissions, TRUE));
    }

    return $permissions;
  }

  /**
   * @internal
   *
   * @param string $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function dispatchString($permissions, $type = NULL, $context = NULL) {
    if('TRUE' === strtoupper($permissions)) {
      return $this->dispatchBoolean(TRUE, $type);
    }

    if('FALSE' === strtoupper($permissions)) {
      return $this->dispatchBoolean(FALSE, $type);
    }

    return $this->externalAccessCheck($permissions, $type, $context);
  }

  /**
   * @internal
   *
   * @param array $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function dispatchArray(array $permissions, $type = NULL, $context = NULL) {
    if(!$permissions) {
      return TRUE;
    }

    reset($permissions);
    $key = key($permissions);
    $value = current($permissions);

    if(!is_numeric($key)) {
      $keyUpper = strtoupper($key);
      if('NO_BYPASS' === $keyUpper) {
        throw new InvalidArgumentValueException("The NO_BYPASS key must be placed highest in the permission hierarchy. Evaluated permissions: " . print_r($permissions, TRUE));
      }
      if('AND' === $keyUpper) {
        return $this->processAND($value, $type, $context);
      }
      if('NAND' === $keyUpper) {
        return $this->processNAND($value, $type, $context);
      }
      if('OR' === $keyUpper) {
        return $this->processOR($value, $type, $context);
      }
      if('NOR' === $keyUpper) {
        return $this->processNOR($value, $type, $context);
      }
      if('XOR' === $keyUpper) {
        return $this->processXOR($value, $type, $context);
      }
      if('NOT' === $keyUpper) {
        return $this->processNOT($value, $type, $context);
      }
      if('TRUE' === $keyUpper || 'FALSE' === $keyUpper) {
        throw new InvalidArgumentValueException("A boolean permission cannot have children. Evaluated permissions: " . print_r($permissions, TRUE));
      }

      if(!is_null($type)) {
        throw new InvalidArgumentValueException("You cannot put a permission type as a descendant to another permission type. Existing type: \"$type\". Evaluated permissions: " . print_r($permissions, TRUE));
      }
      if(!$this->getPermissionTypeCollection()->has($key)) {
        throw new PermissionTypeNotRegisteredException("The permission type \"$key\" could not be found.");
      }
      $type = $key;
    }

    if(is_array($value)) {
      return $this->processOR($value, $type, $context);
    }

    return $this->dispatch($value, $type, $context);
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function processAND($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGateException("The value of an AND gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGateException("The value array of an AND gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    $access = TRUE;
    foreach(array_keys($permissions) as $key) {
      $subpermissions = [$key => $permissions[$key]];
      $access = $access && $this->dispatch($subpermissions, $type, $context);
      if(!$access) {
        break;
      }
    }

    return $access;
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function processNAND($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGateException("The value of a NAND gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGateException("The value array of a NAND gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    return !$this->processAND($permissions, $type, $context);
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function processOR($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGateException("The value of an OR gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGateException("The value array of an OR gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    $access = FALSE;
    foreach(array_keys($permissions) as $key) {
      $subpermissions = [$key => $permissions[$key]];
      $access = $access || $this->dispatch($subpermissions, $type, $context);
      if($access) {
        break;
      }
    }

    return $access;
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function processNOR($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGateException("The value of a NOR gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGateException("The value array of a NOR gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    return !$this->processOR($permissions, $type, $context);
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function processXOR($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGateException("The value of an XOR gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 2) {
     throw new InvalidValueForLogicGateException("The value array of an XOR gate must contain a minimum of two elements. Current value: " . print_r($permissions, TRUE));
    }

    $access = FALSE;
    $count_true = 0;
    $count_false = 0;

    foreach(array_keys($permissions) as $key) {
      $subpermissions = [$key => $permissions[$key]];
      $this_access = $this->dispatch($subpermissions, $type, $context);
      if($this_access) {
        $count_true++;
      }
      else {
        $count_false++;
      }
      if($count_true > 0 && $count_false > 0) {
        $access = TRUE;
        break;
      }
    }

    return $access;
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string|NULL $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function processNOT($permissions, $type = NULL, $context) {
    if(is_array($permissions)) {
      if(count($permissions) != 1) {
        throw new InvalidValueForLogicGateException('A NOT permission must have exactly one child in the value array. Current value: ' . print_r($permissions, TRUE));
      }
    }
    elseif(is_string($permissions)) {
      if(!strlen($permissions)) {
        throw new InvalidValueForLogicGateException('A NOT permission cannot have an empty string as its value.');
      }
    }
    else {
      throw new InvalidValueForLogicGateException("The value of a NOT gate must either be an array or a string. Current value: " . print_r($permissions, TRUE));
    }

    return !$this->dispatch($permissions, $type, $context);
  }

  /**
   * @internal
   *
   * @param array|string|bool $permissions
   * @param string $type
   * @param array|object|NULL $context
   *
   * @return bool
   */
  protected function externalAccessCheck($permission, $type, $context) {
    $permissionTypeCollection = $this->getPermissionTypeCollection();
    if(!$permissionTypeCollection->has($type)) {
      throw new PermissionTypeNotRegisteredException("The permission type \"$type\" could not be found.");
    }

    $access = false;
    $permissionType = $permissionTypeCollection->get($type);
    $access = $permissionType->checkPermission($permission, $context);
    if(!is_bool($access)) {
      throw new InvalidReturnTypeException("The permission type \"$type\" must return a boolean, see Ordermind\LogicalPermissions\PermissionTypeInterface::checkPermission().");
    }

    return $access;
  }
}
