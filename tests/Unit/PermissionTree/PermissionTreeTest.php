<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\StringPermission;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\AlwaysAllowPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\ContextPermissionChecker;
use PHPUnit\Framework\TestCase;

class PermissionTreeTest extends TestCase
{
    public function testGetRootNode()
    {
        $permission = new StringPermission(new AlwaysAllowPermissionChecker(), '');
        $permissionTree = new PermissionTree($permission);

        $this->assertSame($permission, $permissionTree->getRootNode());
    }

    public function testResolve()
    {
        $permission = new StringPermission(new AlwaysAllowPermissionChecker(), '');
        $permissionTree = new PermissionTree($permission);

        $this->assertSame(true, $permissionTree->resolve());
    }

    /**
     * @dataProvider resolveWithContextProvider
     */
    public function testResolveWithContext(bool $expectedResult, bool $context)
    {
        $permission = new StringPermission(new ContextPermissionChecker(), '');
        $permissionTree = new PermissionTree($permission);

        $this->assertSame($expectedResult, $permissionTree->resolve($context));
    }

    public function resolveWithContextProvider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
