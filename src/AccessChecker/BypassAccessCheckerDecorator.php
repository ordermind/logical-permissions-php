<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;

/**
 * @internal
 */
class BypassAccessCheckerDecorator implements BypassAccessCheckerInterface
{
    private ?BypassAccessCheckerInterface $bypassAccessChecker;

    public function __construct(?BypassAccessCheckerInterface $bypassAccessChecker = null)
    {
        $this->bypassAccessChecker = $bypassAccessChecker;
    }

    /**
     * Checks if bypassing access is allowed.
     *
     * @param FullPermissionTree $fullPermissionTree
     * @param array|object|null  $context
     * @param bool               $allowBypass
     *
     * @return bool
     */
    public function isBypassAllowed(FullPermissionTree $fullPermissionTree, $context, bool $allowBypass): bool
    {
        if (!$allowBypass) {
            return false;
        }

        if (!$fullPermissionTree->hasNoBypassTree()) {
            return true;
        }

        return !$fullPermissionTree->getNoBypassTree()->evaluate($context);
    }

    /**
     * {@inheritDoc}
     */
    public function checkBypassAccess($context): bool
    {
        if (is_null($this->bypassAccessChecker)) {
            return false;
        }

        return $this->bypassAccessChecker->checkBypassAccess($context);
    }
}
