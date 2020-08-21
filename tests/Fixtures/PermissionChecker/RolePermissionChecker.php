<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class RolePermissionChecker implements PermissionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'role';
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(string $permission, $context): bool
    {
        if (!empty($context['user']['roles'])) {
            return in_array($permission, $context['user']['roles']);
        }

        return false;
    }
}
