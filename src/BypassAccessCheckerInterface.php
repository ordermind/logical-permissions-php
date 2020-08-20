<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions;

interface BypassAccessCheckerInterface
{
    /**
     * Determines if access checks should be bypassed in the current context.
     *
     * @param array|object|null $context
     *
     * @return bool TRUE if access checks should be bypassed or FALSE if they should not be bypassed
     */
    public function checkBypassAccess($context): bool;
}
