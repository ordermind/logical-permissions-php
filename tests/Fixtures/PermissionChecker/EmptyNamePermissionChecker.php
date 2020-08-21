<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class EmptyNamePermissionChecker implements PermissionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(string $permission, $context): bool
    {
        return true;
    }
}
