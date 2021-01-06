<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;

class BypassAccessCheckerDecorator implements BypassAccessCheckerInterface
{
    protected ?BypassAccessCheckerInterface $bypassAccessChecker;

    public function __construct(?BypassAccessCheckerInterface $bypassAccessChecker = null)
    {
        $this->bypassAccessChecker = $bypassAccessChecker;
    }

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
        if (null === $this->bypassAccessChecker) {
            return false;
        }

        return $this->bypassAccessChecker->checkBypassAccess($context);
    }
}
