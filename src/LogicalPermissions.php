<?php
declare(strict_types=1);

namespace Ordermind\LogicalPermissions;
use Ordermind\LogicalPermissions\LogicalPermissionsInterface;

class LogicalPermissions implements LogicalPermissionsInterface {
  protected $types = [];
  protected $bypass_callback = NULL;

  public function addType(string $name, callable $callback) {
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.'); 
    }
    $types = $this->getTypes();
    $types[$name] = $callback;
    $this->setTypes($types);
    return true;
  }

  public function removeType(string $name) {
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.'); 
    }
    $types = $this->getTypes();
    if(isset($types[$name])) {
      unset($types[$name]);
      $this->setTypes($types);
      return true;
    }
    else {
      return false;
    }
  }
  
  public function typeExists(string $name) {
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.'); 
    }
    $types = $this->getTypes();
    return isset($types[$name]);
  }
  
  public function getTypeCallback(string $name) {
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.'); 
    }
    $types = $this->getTypes();
    if($this->typeExists($name)) {
      return $types[$name];
    }
    else {
      throw new \InvalidArgumentException("The permission type $type has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }
  }

  public function getTypes() {
    return $this->types;
  }

  public function setTypes(array $types) {
    foreach($types as $name => $callback) {
      if(!is_string($name)) {
        throw new \TypeError("The \$types keys must be strings."); 
      }
      if(!$name) {
        throw new \InvalidArgumentException('The name for a type cannot be empty.'); 
      }
      if(!is_callable($callback)) {
        throw new \TypeError("The \$types callbacks must be callables."); 
      }
    }
    $this->types = $types;
    return true;
  }

  public function getBypassCallback() {
    return $this->bypass_callback;
  }

  public function setBypassCallback(callable $callback) {
    $this->bypass_callback = $callback;
    return true;
  }

  public function checkAccess(array $permissions, array $context) {
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
  
  protected function checkBypassAccess(array $context) {
    $bypass_access = FALSE;
    $bypass_callback = $this->getBypassCallback();
    if($bypass_callback) {
      $bypass_access = $bypass_callback($context);
    }
    return $bypass_access;
  }
  
  protected function dispatch($permissions, string $type = NULL, array $context) {
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
              throw new \Exception("You cannot put a permission type as a descendant to another permission type. Existing type: $type. Evaluated permissions: " . print_r($value, true));
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
        throw new \TypeError("A permission must either be a string or an array. Evaluated permissions: " . print_r($permissions, true));
      }
    }
    return $access;
  }
  
  protected function processAND(array $permissions, string $type = NULL, array $context) {
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
  
  protected function processNAND(array $permissions, string $type = NULL, array $context) {
    $access = !$this->processAND($permissions, $type, $context);
    return $access;
  }
  
  protected function processOR(array $permissions, string $type = NULL, array $context) {
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
  
  protected function processNOR(array $permissions, string $type = NULL, array $context) {
    $access = !$this->processOR($permissions, $type, $context);
    return $access;
  }
  
  protected function processXOR(array $permissions, string $type = NULL, array $context) {
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
  
  protected function processNOT($permissions, string $type = NULL, array $context) {
    $access = FALSE;
    if(is_string($permissions)) {
      $access = !$this->externalAccessCheck($permissions, $type, $context);
    }
    else if(is_array($permissions)) {
      if(count($permissions) != 1) {
        throw new \Exception('A NOT permission must have exactly one child. Evaluated permissions: ' . print_r($permissions, TRUE));
      }
      $access = !$this->dispatch($permissions, $type, $context);
    }
    return $access;
  }

  protected function externalAccessCheck(string $permission, string $type, array $context) {
    $access = FALSE;
    if($this->typeExists($type)) {
      $callback = $this->getTypeCallback($type);
      $access = $callback($permission, $context);
      return $access;
    }
    else {
      throw new \InvalidArgumentException("The permission type $type has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }
  }
}
