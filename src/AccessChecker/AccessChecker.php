<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;

class AccessChecker
{
    protected BypassAccessCheckerDecorator $bypassAccessCheckerDecorator;

    public function __construct(BypassAccessCheckerDecorator $bypassAccessCheckerDecorator)
    {
        $this->bypassAccessCheckerDecorator = $bypassAccessCheckerDecorator;
    }

    /**
     * Checks access for a permission tree.
     *
     * @param FullPermissionTree $fullPermissionTree The permission tree to be evaluated
     * @param mixed              $context            (optional) A context that could for example contain the evaluated
     *                                               user and model. Default value is `null`.
     * @param bool               $allowBypass        (optional) Determines whether bypassing access should be allowed.
     *                                               Default value is `true`.
     *
     * @return bool `true` if access is granted or `false` if access is denied
     */
    public function checkAccess(FullPermissionTree $fullPermissionTree, $context = null, bool $allowBypass = true): bool
    {
        $allowBypass = $this->bypassAccessCheckerDecorator->isBypassAllowed(
            $fullPermissionTree,
            $context,
            $allowBypass
        );

        if ($allowBypass && $this->bypassAccessCheckerDecorator->checkBypassAccess($context)) {
            return true;
        }

        return $fullPermissionTree->getMainTree()->evaluate($context);
    }
}
