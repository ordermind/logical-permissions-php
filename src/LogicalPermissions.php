<?php
declare(strict_types=1);

namespace Ordermind\LogicalPermissions;
use Ordermind\LogicalPermissions\LogicalPermissionsInterface;
use Ordermind\LogicalPermissions\PermissionArrayMixedTypesException;

class LogicalPermissions implements LogicalPermissionsInterface {
  protected $types = [];
  protected $bypass_callback = NULL;

  public function addType(string $name, callable $callback) {
    $this->types[$name] = $callback;
    return true;
  }

  public function removeType(string $name) {
    if(isset($this->types[$name])) {
      unset($this->types[$name]);
      return true;
    }
    else {
      return false;
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

  public function checkAccess(array $permissions) {
    $args = func_get_args();
    array_shift($args);

    $access = FALSE;
    $allow_bypass = TRUE;
    if(isset($permissions['no_bypass'])) {
      if(is_bool($permissions['no_bypass'])) {
        $allow_bypass = !$permissions['no_bypass'];
      }
      else if(is_array($permissions['no_bypass'])) {
        $allow_bypass = !$this->dispatch($permissions['no_bypass'], NULL, $args);
      }
    }
    if($allow_bypass && $this->checkBypassAccess($args)) {
      $access = TRUE;
    }
    else {
      $access = $this->dispatch($permissions, NULL, $args);
    }
    return $access;
  }
  
  protected function checkBypassAccess(array $args = []) {
    $bypass_access = FALSE;
    $bypass_callback = $this->getBypassCallback();
    if($bypass_callback) {
      $bypass_access = call_user_func_array($bypass_callback, $args);
    }
    return $bypass_access;
  }
  
  protected function dispatch($permissions, string $type = NULL, array $args = []) {
    $access = FALSE;
    $key = '';
    if(is_string($permissions)) {
      $this->callMethod($permissions, $type, $args);
    }
    elseif(is_array($permissions)) {
      if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
        $access = $this->processOR($permissions, $type, $args);
      }
      else { //Associative array
        reset($permissions);
        $key = key($permissions);
        if(is_numeric($key)) {
          throw new PermissionArrayMixedTypesException($permissions);
        }
        $value = current($permissions);
        if($key === 'AND') {
          $access = $this->processAND($value, $type, $args);
        }
        elseif($key === 'NAND') {
          $access = $this->processNAND($value, $type, $args);
        }
        elseif($key === 'OR') {
          $access = $this->processOR($value, $type, $args);
        }
        elseif($key === 'NOR') {
          $access = $this->processNOR($value, $type, $args);
        }
        elseif($key === 'XOR') {
          $access = $this->processXOR($value, $type, $args);
        }
        elseif($key === 'NOT') {
          $access = $this->processNOT($value, $type, $args);
        }
        else {
          $type = $key;
          $access = $this->dispatch($value, $type, $args);
        }
      }
    }
    return $access;
  }
  
  protected function processAND(array $permissions, string $type = NULL, array $args = []) {
    $access = TRUE;
    if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
      foreach($permissions as $permission) {
        $access = $access && $this->callMethod($permission, $type, $args);
        if(!$access) {
          break; 
        }
      }
    }
    else {
      foreach($permissions as $key => $subpermissions) {
        if(is_numeric($key)) {
          throw new PermissionArrayMixedTypesException($permissions);
        }
        $access = $access && $this->dispatch($subpermissions, $type, $args);
        if(!$access) {
          break; 
        }
      }
    }
    return $access;
  }
  
  protected function processNAND(array $permissions, string $type = NULL, array $args = []) {
    $access = !$this->processAND($permissions, $type, $args);
    return $access;
  }
  
  protected function processOR(array $permissions, string $type = NULL, array $args = []) {
    $access = FALSE;
    if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
      foreach($permissions as $permission) {
        $access = $access || $this->callMethod($permission, $type, $args);
        if($access) {
          break; 
        }
      }
    }
    else {
      foreach($permissions as $key => $subpermissions) {
        if(is_numeric($key)) {
          throw new PermissionArrayMixedTypesException($permissions);
        }
        $access = $access || $this->dispatch($subpermissions, $type, $args);
        if($access) {
          break; 
        }
      }
    }
    return $access;
  }
  
  protected function processNOR(array $permissions, string $type = NULL, array $args = []) {
    $access = !$this->processOR($permissions, $type, $args);
    return $access;
  }
  
  protected function processXOR(array $permissions, string $type = NULL, array $args = []) {
    $access = FALSE;
    $count_true = 0;
    $count_false = 0;

    if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
      foreach($permissions as $permission) {
        $this_access = $this->callMethod($permission, $type, $args);
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
    }
    else {
      foreach($permissions as $key => $subpermissions) {
        if(is_numeric($key)) {
          throw new PermissionArrayMixedTypesException($permissions);
        }
        $this_access = $this->dispatch($subpermissions, $type, $args);
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
    }
    return $access;
  }
  
  protected function processNOT($permissions, string $type = NULL, array $args = []) {
    $access = FALSE;
    if(is_string($permissions)) {
      $access = !$this->callMethod($permissions, $type, $args);
    }
    else if(is_array($permissions)) {
      if(count($permissions) != 1) {
        throw new Exception('A NOT permission must have exactly one child. Evaluated permissions: ' . print_r($permissions, TRUE));
      }
      $access = !$this->dispatch($permissions, $type, $args);
    }
    return $access;
  }

  protected function callMethod(string $permission, string $type, array $args = []) {
    $access = FALSE;
    $types = $this->getTypes();
    if(isset($types[$type])) {
      $callback = $types[$type];
      $access = call_user_func_array($callback, array_merge([$permission], $args));
      return $access;
    }
    else {
      throw new Exception("The permission type $type has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }
  }
}
