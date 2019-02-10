<?php

namespace Ordermind\LogicalPermissions\Test;

// Solves issue with different parent classes in different versions of PHPUnits. Each test class should extend this.
if(class_exists('PHPUnit_Framework_TestCase')) {
  class LogicalPermissionsPHPUnitShim extends \PHPUnit_Framework_TestCase {
    public function expectException($class) {
      $this->setExpectedException($class);
    }
  }
}
else {
  class LogicalPermissionsPHPUnitShim extends \PHPUnit\Framework\TestCase {}
}
