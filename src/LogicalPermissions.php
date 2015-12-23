<?php

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\Interfaces\LogicalPermissionsInterface;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentTypeException;
use Ordermind\LogicalPermissions\Exceptions\InvalidArgumentValueException;
use Ordermind\LogicalPermissions\Exceptions\InvalidValueForLogicGate;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;

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
      throw new PermissionTypeNotRegisteredException("The permission type $name has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }

    $types = $this->getTypes();
    return $types[$name];
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

  public function checkAccess($permissions, $context) {
    if(!is_array($permissions)) {
      throw new InvalidArgumentTypeException('The permissions parameter must be an array.'); 
    }
    if(!is_array($context)) {
      throw new InvalidArgumentTypeException('The context parameter must be an array.'); 
    }

    $access = FALSE;
    $allow_bypass = TRUE;
    if(isset($permissions['no_bypass'])) {
      if(is_bool($permissions['no_bypass'])) {
        $allow_bypass = !$permissions['no_bypass'];
      }
      else if(is_array($permissions['no_bypass'])) {
        $allow_bypass = !$this->dispatch($permissions['no_bypass'], NULL, $context);
      }
      unset($permissions['no_bypass']);
    }
    if($allow_bypass && $this->checkBypassAccess($context)) {
      $access = TRUE;
    }
    else {
      $access = $this->processOR($permissions, NULL, $context);
    }
    return $access;
  }
  
  protected function checkBypassAccess($context) {
    $bypass_access = FALSE;
    $bypass_callback = $this->getBypassCallback();
    if(is_callable($bypass_callback)) {
      $bypass_access = $bypass_callback($context);
    }
    return $bypass_access;
  }
  
  protected function dispatch($permissions, $type = NULL, $context) {
    $access = FALSE;
    if($permissions) {
      if(is_string($permissions)) {
        $access = $this->externalAccessCheck($permissions, $type, $context);
      }
      elseif(is_array($permissions)) {
        reset($permissions);
        $key = key($permissions);
        $value = current($permissions);
        if($key === 'AND') {
          $access = $this->processAND($value, $type, $context);
        }
        elseif($key === 'NAND') {
          $access = $this->processNAND($value, $type, $context);
        }
        elseif($key === 'OR') {
          $access = $this->processOR($value, $type, $context);
        }
        elseif($key === 'NOR') {
          $access = $this->processNOR($value, $type, $context);
        }
        elseif($key === 'XOR') {
          $access = $this->processXOR($value, $type, $context);
        }
        elseif($key === 'NOT') {
          $access = $this->processNOT($value, $type, $context);
        }
        else {
          if(!is_numeric($key)) {
            if(is_null($type)) {
              $type = $key;
            }
            else {
              throw new InvalidArgumentValueException("You cannot put a permission type as a descendant to another permission type. Existing type: $type. Evaluated permissions: " . print_r($value, TRUE));
            }
          }
          if(is_array($value)) {
            $access = $this->processOR($value, $type, $context);
          }
          else {
            $access = $this->dispatch($value, $type, $context);
          }
        }
      }
      else {
        throw new InvalidArgumentTypeException("A permission must either be a string or an array. Evaluated permissions: " . print_r($permissions, TRUE));
      }
    }
    return $access;
  }
  
  protected function processAND($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGate("The value of an AND gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGate("The value array of an AND gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
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
      throw new InvalidValueForLogicGate("The value of a NAND gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGate("The value array of a NAND gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    $access = !$this->processAND($permissions, $type, $context);
    return $access;
  }
  
  protected function processOR($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGate("The value of an OR gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGate("The value array of an OR gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
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
      throw new InvalidValueForLogicGate("The value of a NOR gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 1) {
      throw new InvalidValueForLogicGate("The value array of a NOR gate must contain a minimum of one element. Current value: " . print_r($permissions, TRUE));
    }

    $access = !$this->processOR($permissions, $type, $context);
    return $access;
  }
  
  protected function processXOR($permissions, $type = NULL, $context) {
    if(!is_array($permissions)) {
      throw new InvalidValueForLogicGate("The value of an XOR gate must be an array. Current value: " . print_r($permissions, TRUE));
    }
    if(count($permissions) < 2) {
     throw new InvalidValueForLogicGate("The value array of an XOR gate must contain a minimum of two elements. Current value: " . print_r($permissions, TRUE));
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
    if(!is_array($permissions) && !is_string($permissions)) {
      throw new InvalidValueForLogicGate("The value of a NOT gate must either be an array or a string. Current value: " . print_r($permissions, TRUE));
    }
    if(is_array($permissions)) {
      if(count($permissions) != 1) {
        throw new InvalidValueForLogicGate('A NOT permission must have exactly one child in the value array. Current value: ' . print_r($permissions, TRUE));
      }
    }
    elseif(is_string($permissions)) {
      if(!$permissions) {
        throw new InvalidValueForLogicGate('A NOT permission cannot have an empty string as its value.');
      }
    }

    $access = !$this->dispatch($permissions, $type, $context);
    return $access;
  }

  protected function externalAccessCheck($permission, $type, $context) {
    if(!$this->typeExists($type)) {
      throw new PermissionTypeNotRegisteredException("The permission type $type has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }

    $access = false;
    $callback = $this->getTypeCallback($type);
    if(is_callable($callback)) {
      $access = $callback($permission, $context);
    }
    return $access;
  }
}
