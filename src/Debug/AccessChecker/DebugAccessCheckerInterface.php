<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\AccessChecker;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;

interface DebugAccessCheckerInterface
{
    /**
     * Checks access for a permission tree and returns the result together with debug information.
     *
     * @param FullPermissionTree $fullPermissionTree The permission tree to be evaluated
     * @param mixed              $context            (optional) A context that could for example contain the evaluated
     *                                               user and model. Default value is NULL.
     * @param bool               $allowBypass        (optional) Determines whether bypassing access should be allowed.
     *                                               Default value is TRUE.
     *
     * @return DebugAccessCheckerResult
     */
    public function checkAccess(
        FullPermissionTree $fullPermissionTree,
        $context = null,
        bool $allowBypass = true
    ): DebugAccessCheckerResult;
}
