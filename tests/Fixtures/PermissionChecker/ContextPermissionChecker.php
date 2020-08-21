<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class ContextPermissionChecker implements PermissionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'check_context';
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(string $permission, $context): bool
    {
        return (bool) $context;
    }
}
