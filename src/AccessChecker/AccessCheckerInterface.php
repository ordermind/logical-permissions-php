<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;

interface AccessCheckerInterface
{
    /**
     * Checks access for a permission tree.
     *
     * @param FullPermissionTree $fullPermissionTree The permission tree to be evaluated
     * @param array|object|null  $context            (optional) A context that could for example contain the evaluated
     *                                               user and model. Default value is NULL.
     * @param bool               $allowBypass        (optional) Determines whether bypassing access should be allowed.
     *                                               Default value is TRUE.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkAccess(
        FullPermissionTree $fullPermissionTree,
        $context = null,
        bool $allowBypass = true
    ): bool;
}
