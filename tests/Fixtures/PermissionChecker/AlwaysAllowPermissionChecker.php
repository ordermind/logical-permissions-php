<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class AlwaysAllowPermissionChecker implements PermissionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'always_allow';
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(string $permission, $context): bool
    {
        return true;
    }
}
