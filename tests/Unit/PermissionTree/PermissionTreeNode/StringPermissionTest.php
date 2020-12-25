<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\AlwaysAllowPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\ContextPermissionChecker;
use PHPUnit\Framework\TestCase;

class StringPermissionTest extends TestCase
{
    public function testGetPermissionType()
    {
        $permissionChecker = new AlwaysAllowPermissionChecker();
        $permission = new StringPermission($permissionChecker, '', '');

        $this->assertSame($permissionChecker, $permission->getPermissionChecker());
    }

    public function testGetPermission()
    {
        $permissionChecker = new AlwaysAllowPermissionChecker();
        $permission = new StringPermission($permissionChecker, 'test', 'test');

        $this->assertSame('test', $permission->getPermissionValue());
    }

    public function testGetValue()
    {
        $permissionChecker = new AlwaysAllowPermissionChecker();
        $permission = new StringPermission($permissionChecker, '', '');

        $this->assertSame(true, $permission->getValue());
    }

    /**
     * @dataProvider getValueWithContextProvider
     */
    public function testGetValueWithContext(bool $expectedResult, bool $context)
    {
        $permissionChecker = new ContextPermissionChecker();
        $permission = new StringPermission($permissionChecker, '', '');

        $this->assertSame($expectedResult, $permission->getValue($context));
    }

    public function getValueWithContextProvider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
