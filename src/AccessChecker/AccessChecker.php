<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use TypeError;

/**
 * @internal
 */
class AccessChecker implements AccessCheckerInterface
{
    /**
     * @var BypassAccessCheckerInterface|null
     */
    protected $bypassAccessChecker;

    /**
     * AccessChecker constructor.
     *
     * @param BypassAccessCheckerInterface|null $bypassAccessChecker
     */
    public function __construct(?BypassAccessCheckerInterface $bypassAccessChecker = null)
    {
        $this->bypassAccessChecker = $bypassAccessChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess(FullPermissionTree $fullPermissionTree, $context = null, bool $allowBypass = true): bool
    {
        if (!is_null($context) && !is_array($context) && !is_object($context)) {
            throw new TypeError('The context parameter must be an array or object.');
        }

        $allowBypass = $this->isBypassAllowed($fullPermissionTree, $context, $allowBypass);

        if ($allowBypass && $this->checkBypassAccess($context)) {
            return true;
        }

        return $fullPermissionTree->getMainTree()->resolve($context);
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
    protected function isBypassAllowed(FullPermissionTree $fullPermissionTree, $context, bool $allowBypass): bool
    {
        if (!$allowBypass) {
            return false;
        }

        if (!$fullPermissionTree->hasNoBypassTree()) {
            return $allowBypass;
        }

        return !$fullPermissionTree->getNoBypassTree()->resolve($context);
    }

    /**
     * Checks if access should be bypassed.
     *
     * @param array|object|null $context
     *
     * @return bool
     */
    protected function checkBypassAccess($context): bool
    {
        if (is_null($this->bypassAccessChecker)) {
            return false;
        }

        return $this->bypassAccessChecker->checkBypassAccess($context);
    }
}
