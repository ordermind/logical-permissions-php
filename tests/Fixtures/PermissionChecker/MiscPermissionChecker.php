<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class MiscPermissionChecker implements PermissionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'misc';
    }

    /**
     * {@inheritDoc}
     */
    public function checkPermission(string $permission, $context): bool
    {
        return !empty($context['user'][$permission]);
    }
}
