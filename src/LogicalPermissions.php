<?php

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGateException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\InvalidCallbackReturnTypeException;

class LogicalPermissions implements LogicalPermissionsInterface {
  protected $types = [];
  protected $bypass_callback = NULL;

  public function addType($name, $callback) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name parameter cannot be empty.');
    }
    if(in_array($name, $core_keys = $this->getCorePermissionKeys())) {
      throw new InvalidArgumentValueException("The name parameter has the illegal value \"$name\". It cannot be one of the following values: " . implode(',', $core_keys));
    }
    if($this->typeExists($name)) {
      throw new PermissionTypeAlreadyExistsException("The type \"$name\" already exists! If you want to change the callback for an existing type, please use LogicalPermissions::setTypeCallback().");
    }
    if(!is_callable($callback)) {
      throw new InvalidArgumentTypeException('The callback parameter must be a callable data type.');
    }

    $types = $this->getTypes();
    $types[$name] = $callback;
    $this->setTypes($types);
  }

  public function removeType($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name parameter cannot be empty.');
    }
    if(!$this->typeExists($name)) {
      throw new PermissionTypeNotRegisteredException("The permission type \"$name\" has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }

    $types = $this->getTypes();
    unset($types[$name]);
    $this->setTypes($types);
  }

  public function typeExists($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name parameter cannot be empty.');
    }

    $types = $this->getTypes();
    return isset($types[$name]);
  }

  public function getTypeCallback($name) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name parameter cannot be empty.');
    }
    if(!$this->typeExists($name)) {
      throw new PermissionTypeNotRegisteredException("The permission type \"$name\" has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }

    $types = $this->getTypes();
    return $types[$name];
  }

  public function setTypeCallback($name, $callback) {
    if(!is_string($name)) {
      throw new InvalidArgumentTypeException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new InvalidArgumentValueException('The name parameter cannot be empty.');
    }
    if(!$this->typeExists($name)) {
      throw new PermissionTypeNotRegisteredException("The permission type \"$name\" has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }
    if(!is_callable($callback)) {
      throw new InvalidArgumentTypeException('The callback parameter must be a callable data type.');
    }

    $types = $this->getTypes();
    $types[$name] = $callback;
    $this->setTypes($types);
  }

  public function getTypes() {
    return $this->types;
  }

  public function setTypes($types) {
    if(!is_array($types)) {
      throw new InvalidArgumentTypeException('The types parameter must be an array.');
    }
    foreach($types as $name => $callback) {
      if(!is_string($name)) {
        throw new InvalidArgumentValueException("The \$types keys must be strings.");
      }
      if(!$name) {
        throw new InvalidArgumentValueException('The name for a type cannot be empty.');
      }
      if(in_array(strtoupper($name), $core_keys = $this->getCorePermissionKeys())) {
        throw new InvalidArgumentValueException("The name for a type has the illegal value \"$name\". It cannot be one of the following values: " . implode(',', $core_keys));
      }
      if(!is_callable($callback)) {
        throw new InvalidArgumentValueException("The \$types callbacks must be callables.");
      }
    }

    $this->types = $types;
  }

  public function getBypassCallback() {
    return $this->bypass_callback;
  }

  public function setBypassCallback($callback) {
    if(!is_callable($callback)) {
      throw new InvalidArgumentTypeException('The callback parameter must be a callable data type.');
    }

    $this->bypass_callback = $callback;
  }

  public function getValidPermissionKeys() {
    return array_merge($this->getCorePermissionKeys(), array_keys($this->getTypes()));
  }

  public function checkAccess($permissions, $context = [], $allow_bypass = TRUE) {
    if(!is_array($permissions) && !is_string($permissions) && !is_bool($permissions)) {
      throw new InvalidArgumentTypeException('The permissions parameter must be either an array or in certain cases a string or boolean.');
    }
    if(!is_array($context)) {
      throw new InvalidArgumentTypeException('The context parameter must be an array.');
    }
    if(!is_bool($allow_bypass)) {
      throw new InvalidArgumentTypeException('The allow_bypass parameter must be a boolean.');
    }

    // uppercasing of no_bypass key for backward compatibility
    if(is_array($permissions) && array_key_exists('no_bypass', $permissions)) {
      $permissions['NO_BYPASS'] = $permissions['no_bypass'];
      unset($permissions['no_bypass']);
    }

    if(is_array($permissions) && array_key_exists('NO_BYPASS', $permissions)) {
      if($allow_bypass) {
        if(is_bool($permissions['NO_BYPASS'])) {
          $allow_bypass = !$permissions['NO_BYPASS'];
        }
        else if(is_string($permissions['NO_BYPASS'])) {
          $no_bypass_upper = strtoupper($permissions['NO_BYPASS']);
          if(!in_array($no_bypass_upper, array('TRUE', 'FALSE'))) {
            throw new InvalidArgumentValueException('The NO_BYPASS value must be a boolean, a boolean string or an array. Current value: ' . print_r($permissions['NO_BYPASS'], TRUE));
          }

          if($no_bypass_upper === 'TRUE') {
            $allow_bypass = FALSE;
          }
          else if($no_bypass_upper === 'FALSE') {
            $allow_bypass = TRUE;
          }
        }
        else if(is_array($permissions['NO_BYPASS'])) {
          $allow_bypass = !$this->processOR($permissions['NO_BYPASS'], NULL, $context);
        }
        else {
          throw new InvalidArgumentValueException('The NO_BYPASS value must be a boolean, a boolean string or an array. Current value: ' . print_r($permissions['NO_BYPASS'], TRUE));
        }
      }
      unset($permissions['NO_BYPASS']);
    }

    if($allow_bypass && $this->checkBypassAccess($context)) {
      return TRUE;
    }
    if(is_bool($permissions)) {
      return $this->dispatch($permissions);
    }
    if(is_string($permissions)) {
      return $this->dispatch($permissions);
    }
    if(is_array($permissions) && count($permissions)) {
      return $this->processOR($permissions, NULL, $context);
    }
    return TRUE;
  }

  protected function getCorePermissionKeys() {
    return ['NO_BYPASS', 'AND', 'NAND', 'OR', 'NOR', 'XOR', 'NOT', 'TRUE', 'FALSE'];
  }

  protected function checkBypassAccess($context) {
    $bypass_callback = $this->getBypassCallback();
    if(!is_callable($bypass_callback)) {
      return FALSE;
    }

    $bypass_access = $bypass_callback($context);
    if(!is_bool($bypass_access)) {
      throw new InvalidCallbackReturnTypeException('The bypass access callback must return a boolean.');
    }
    return $bypass_access;
  }

  protected function dispatch($permissions, $type = NULL, $context = []) {
    if(is_bool($permissions)) {
      if($permissions === TRUE) {
        if(!is_null($type)) {
          throw new InvalidArgumentValueException("You cannot put a boolean permission as a descendant to a permission type. Existing type: $type. Evaluated permissions: " . print_r($permissions, TRUE));
        }
        return TRUE;
      }
      if($permissions === FALSE) {
        if(!is_null($type)) {
          throw new InvalidArgumentValueException("You cannot put a boolean permission as a descendant to a permission type. Existing type: $type. Evaluated permissions: " . print_r($permissions, TRUE));
        }
        return FALSE;
      }
    }
    if(is_string($permissions)) {
      if(strtoupper($permissions) === 'TRUE') {
        if(!is_null($type)) {
          throw new InvalidArgumentValueException("You cannot put a boolean permission as a descendant to a permission type. Existing type: $type. Evaluated permissions: " . print_r($permissions, TRUE));
        }
        return TRUE;
      }
      if(strtoupper($permissions) === 'FALSE') {
        if(!is_null($type)) {
          throw new InvalidArgumentValueException("You cannot put a boolean permission as a descendant to a permission type. Existing type: $type. Evaluated permissions: " . print_r($permissions, TRUE));
        }
        return FALSE;
      }
      return $this->externalAccessCheck($permissions, $type, $context);
    }
    if(is_array($permissions)) {
      reset($permissions);
      $key = key($permissions);
      $value = current($permissions);
      if(is_string($key)) {
        $key_upper = strtoupper($key);
        if($key_upper === 'NO_BYPASS') {
          throw new InvalidArgumentValueException("The NO_BYPASS key must be placed highest in the permission hierarchy. Evaluated permissions: " . print_r($permissions, TRUE));
        }
        if($key_upper === 'AND') {
          return $this->processAND($value, $type, $context);
        }
        if($key_upper === 'NAND') {
          return $this->processNAND($value, $type, $context);
        }
        if($key_upper === 'OR') {
          return $this->processOR($value, $type, $context);
        }
        if($key_upper === 'NOR') {
          return $this->processNOR($value, $type, $context);
        }
        if($key_upper === 'XOR') {
          return $this->processXOR($value, $type, $context);
        }
        if($key_upper === 'NOT') {
          return $this->processNOT($value, $type, $context);
        }
        if($key_upper === 'TRUE' || $key_upper === 'FALSE') {
          throw new InvalidArgumentValueException("A boolean permission cannot have children. Evaluated permissions: " . print_r($permissions, TRUE));
        }

        if(!is_null($type)) {
          throw new InvalidArgumentValueException("You cannot put a permission type as a descendant to another permission type. Existing type: $type. Evaluated permissions: " . print_r($permissions, TRUE));
        }
        $type = $key;
      }
      if(is_array($value)) {
        return $this->processOR($value, $type, $context);
      }
      return $this->dispatch($value, $type, $context);
    }
    throw new InvalidArgumentTypeException("A permission must either be a boolean, a string or an array. Evaluated permissions: " . print_r($permissions, TRUE));
  }

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

  protected function processNAND($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGateException("The value of a NAND gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGateException("The value array of a NAND gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    return !$this->processAND($permissions, $type, $context);
  }

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

  protected function processNOR($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGateException("The value of a NOR gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGateException("The value array of a NOR gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    return !$this->processOR($permissions, $type, $context);
  }

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

  protected function processNOT($permissions, $type = NULL, $context) {
    if(is_array($permissions)) {
      if(count($permissions) != 1) {
        throw new InvalidValueForLogicGateException('A NOT permission must have exactly one child in the value array. Current value: ' . print_r($permissions, TRUE));
      }
    }
    elseif(is_string($permissions)) {
      if($permissions === '') {
        throw new InvalidValueForLogicGateException('A NOT permission cannot have an empty string as its value.');
      }
    }
    else {
      throw new InvalidValueForLogicGateException("The value of a NOT gate must either be an array or a string. Current value: " . print_r($permissions, TRUE));
    }

    return !$this->dispatch($permissions, $type, $context);
  }

  protected function externalAccessCheck($permission, $type, $context) {
    if(!$this->typeExists($type)) {
      throw new PermissionTypeNotRegisteredException("The permission type \"$type\" has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }

    $access = false;
    $callback = $this->getTypeCallback($type);
    if(is_callable($callback)) {
      $access = $callback($permission, $context);
      if(!is_bool($access)) {
        throw new InvalidCallbackReturnTypeException("The registered callback for the permission type \"$type\" must return a boolean.");
      }
    }
    return $access;
  }
}
