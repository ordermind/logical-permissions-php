<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class ErroneousNameTypePermissionChecker implements PermissionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(string $permission, $context): bool
    {
        return true;
    }
}
