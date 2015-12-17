<?php
declare(strict_types=1);

namespace Ordermind\LogicalPermissions;
use Ordermind\LogicalPermissions\LogicalPermissionsInterface;

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

  public function checkAccess(array $permissions, array $context = []) {
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
      $access = $this->dispatch($permissions, NULL, $context);
    }
    return $access;
  }
  
  protected function checkBypassAccess(array $context = []) {
    $bypass_access = FALSE;
    $bypass_callback = $this->getBypassCallback();
    if($bypass_callback) {
      $bypass_access = $bypass_callback($context);
    }
    return $bypass_access;
  }
  
  protected function dispatch($permissions, string $type = NULL, array $context = []) {
    $access = FALSE;
    $key = '';
    if(is_string($permissions)) {
      $access = $this->externalAccessCheck($permissions, $type, $context);
    }
    elseif(is_array($permissions)) {
      if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
        $access = $this->processOR($permissions, $type, $context);
      }
      else { //Associative array
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
          if(!is_null($type) {
            throw new Exception("You cannot put a permission type as a descendant to another permission type. Existing type: $type. Evaluated permissions: " . print_r($value, true));
          }
          $type = $key;
          $access = $this->dispatch($value, $type, $context);
        }
      }
    }
    return $access;
  }
  
  protected function processAND(array $permissions, string $type = NULL, array $context = []) {
    $access = TRUE;
    foreach($permissions as $key => $subpermissions) {
      $access = $access && $this->dispatch($subpermissions, $type, $context);
      if(!$access) {
        break; 
      }
    }
    return $access;
  }
  
  protected function processNAND(array $permissions, string $type = NULL, array $context = []) {
    $access = !$this->processAND($permissions, $type, $context);
    return $access;
  }
  
  protected function processOR(array $permissions, string $type = NULL, array $context = []) {
    $access = FALSE;
    foreach($permissions as $key => $subpermissions) {
      $access = $access || $this->dispatch($subpermissions, $type, $context);
      if($access) {
        break; 
      }
    }
    return $access;
  }
  
  protected function processNOR(array $permissions, string $type = NULL, array $context = []) {
    $access = !$this->processOR($permissions, $type, $context);
    return $access;
  }
  
  protected function processXOR(array $permissions, string $type = NULL, array $context = []) {
    $access = FALSE;
    $count_true = 0;
    $count_false = 0;

    foreach($permissions as $key => $subpermissions) {
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
  
  protected function processNOT($permissions, string $type = NULL, array $context = []) {
    $access = FALSE;
    if(is_string($permissions)) {
      $access = !$this->externalAccessCheck($permissions, $type, $context);
    }
    else if(is_array($permissions)) {
      if(count($permissions) != 1) {
        throw new Exception('A NOT permission must have exactly one child. Evaluated permissions: ' . print_r($permissions, TRUE));
      }
      $access = !$this->dispatch($permissions, $type, $context);
    }
    return $access;
  }

  protected function externalAccessCheck(string $permission, string $type, array $context = []) {
    $access = FALSE;
    $types = $this->getTypes();
    if(isset($types[$type])) {
      $callback = $types[$type];
      $access = $callback($permission, $context);
      return $access;
    }
    else {
      throw new Exception("The permission type $type has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }
  }
}
