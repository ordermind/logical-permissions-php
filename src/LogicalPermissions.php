<?php

namespace Ordermind\LogicalPermissions;
use Ordermind\LogicalPermissions\LogicalPermissionsInterface;
use Ordermind\LogicalPermissions\PermissionArrayMixedTypesException;
use Ordermind\LogicalPermissions\PermissionChildNotArrayException;

class LogicalPermissions implements LogicalPermissionsInterface {
  protected $types = [];
  protected $bypass_callback = NULL;

  public function addType($name, $callback) {
    $this->types[$name] = $callback;
  }

  public function removeType($name) {
    unset($this->types[$name]);
  }

  public function getTypes() {
    return $this->types;
  }

  public function setTypes($types) {
    $this->types = $types;
  }

  public function getBypassCallback() {
    return $this->bypass_callback;
  }

  public function setBypassCallback($callback) {
    $this->bypass_callback = $callback;
  }

  public function checkAccess($permissions) {
    $access = FALSE;
    $allow_bypass = TRUE;
    if(isset($permissions['no_bypass'])) {
      if(is_bool($permissions['no_bypass'])) {
        $allow_bypass = !$permissions['no_bypass'];
      }
      else if(is_array($permissions['no_bypass'])) {
        $allow_bypass = !$this->dispatch($permissions); //LÄGG TILL ARGUMENT
      }
    }
    if($allow_bypass && $this->checkBypassAccess() { //LÄGG TILL ARGUMENT
      $access = TRUE;
    }
    else {
      $access = $this->dispatch($permissions); //LÄGG TILL ARGUMENT
    }
    return $access;
  }
  
  protected function checkBypassAccess() {
    $bypass_access = FALSE;
    $bypass_callback = $this->getBypassCallback();
    if($bypass_callback) {
      $bypass_access = $bypass_callback(); //LÄGG TILL ARGUMENT
    }
    return $bypass_access;
  }
  
  protected function dispatch($permissions, $type = NULL) {
    $access = FALSE;
    $key = '';
    if(is_string($permissions)) {
      $this->callMethod($permissions, $type); //LÄGG TILL ARGUMENT
    }
    elseif(is_array($permissions)) {
      if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
        $access = this->processOR($permissions, $type); //LÄGG TILL ARGUMENT
      }
      else { //Associative array
        reset($permissions);
        $key = key($permissions);
        if(is_numeric($key)) {
          throw new PermissionArrayMixedTypesException($permissions);
        }
        $value = current($permissions);
        if($key === 'AND') {
          $access = $this->processAND($value, $type); //LÄGG TILL ARGUMENT 
        }
        elseif($key === 'NAND') {
          $access = $this->processNAND($value, $type); //LÄGG TILL ARGUMENT
        }
        elseif($key === 'OR') {
          $access = $this->processOR($value, $type); //LÄGG TILL ARGUMENT 
        }
        elseif($key === 'NOR') {
          $access = $this->processNOR($value, $type); //LÄGG TILL ARGUMENT 
        }
        elseif($key === 'XOR') {
          $access = $this->processXOR($value, $type); //LÄGG TILL ARGUMENT 
        }
        elseif($key === 'NOT') {
          $access = $this->processNOT($value, $type); //LÄGG TILL ARGUMENT
        }
        else {
          $type = $key;
          $access = $this->dispatch($value, $type); //LÄGG TILL ARGUMENT
        }
      }
    }
    return $access;
  }
  
  protected function processAND($permissions, $type) {
    if(is_array($permissions)) {
      $access = TRUE;
      if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
        foreach($permissions as $permission) {
          $access = $access && $this->callMethod($permission, $type); //LÄGG TILL ARGUMENT
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
          $access = $access && $this->dispatch($subpermissions, $type); //LÄGG TILL ARGUMENT
          if(!$access) {
            break; 
          }
        }
      }
      return $access;
    }
    else {
      throw new PermissionChildNotArrayException('AND', $permissions);
    }
  }
  
  protected function processNAND($permissions, $type) {
    if(is_array($permissions)) {
      $access = !$this->processAND($permissions, $type); //LÄGG TILL ARGUMENT
      return $access;
    }
    else {
      throw new PermissionChildNotArrayException('NAND', $permissions);
    }
  }
  
  protected function processOR($permissions, $type) {
    if(is_array($permissions)) {
      $access = FALSE;
      if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
        foreach($permissions as $permission) {
          $access = $access || $this->callMethod($permission, $type); //LÄGG TILL ARGUMENT
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
          $access = $access || $this->dispatch($subpermissions, $type); //LÄGG TILL ARGUMENT
          if($access) {
            break; 
          }
        }
      }
      return $access;
    }
    else {
      throw new PermissionChildNotArrayException('OR', $permissions);
    }
  }
  
  protected function processNOR($permissions, $type) {
    if(is_array($permissions)) {
      $access = !$this->processOR($permissions, $type); //LÄGG TILL ARGUMENT
      return $access;
    }
    else {
      throw new PermissionChildNotArrayException('NOR', $permissions);
    }
  }
  
  protected function processXOR($permissions, $type) {
    if(is_array($permissions)) {
      $access = FALSE;
      $count_true = 0;
      $count_false = 0;

      if(array_keys($permissions) === range(0, count($permissions) - 1)) { //Completely sequential array
        foreach($permissions as $permission) {
          $this_access = $this->callMethod($permission, $type); //LÄGG TILL ARGUMENT
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
          $this_access = $this->dispatch($subpermissions, $type); //LÄGG TILL ARGUMENT
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
    else {
      throw new PermissionChildNotArrayException('XOR', $permissions);
    }
  }
  
  protected function processNOT($permissions, $type) {
    $access = FALSE;
    if(is_string($permissions)) {
      $access = !$this->callMethod($permissions, $type); //LÄGG TILL ARGUMENT 
    }
    else if(is_array($permissions)) {
      if(count($permissions) != 1) {
        throw new Exception('A NOT permission must have exactly one child. Evaluated permissions: ' . print_r($permissions, TRUE));
      }
      $access = !$this->dispatch($permissions, $type); //LÄGG TILL ARGUMENT 
    }
    return $access;
  }

  protected function callMethod($permissions, $type) {
    $access = FALSE;
    $types = $this->getTypes();
    if(isset($types[$type])) {
      $callback = $types[$type];
      $access = $callback(); //LÄGG TILL ARGUMENT
      return $access;
    }
    else {
      throw new Exception("The permission type $type has not been registered. Please use LogicalPermissions::addType() or LogicalPermissions::setTypes() to register permission types.");
    }
  }
}
