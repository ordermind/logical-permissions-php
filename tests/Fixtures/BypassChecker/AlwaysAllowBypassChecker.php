<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class AlwaysAllowBypassChecker implements BypassAccessCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function checkBypassAccess($context): bool
    {
        return true;
    }
}
