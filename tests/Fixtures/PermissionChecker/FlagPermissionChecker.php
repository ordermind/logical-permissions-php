<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class FlagPermissionChecker implements PermissionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'flag';
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(string $permission, $context): bool
    {
        return !empty($context['user'][$permission]);
    }
}
