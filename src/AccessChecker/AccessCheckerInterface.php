<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;

interface AccessCheckerInterface
{
    /**
     * Checks access for a permission tree.
     *
     * @param RawPermissionTree $rawPermissionTree The permission tree to be evaluated
     * @param array|object|null $context           (optional) A context that could for example contain the evaluated
     *                                             user and document. Default value is NULL.
     * @param bool              $allowBypass       (optional) Determines whether bypassing access should be allowed.
     *                                             Default value is TRUE.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkAccess(RawPermissionTree $rawPermissionTree, $context = null, bool $allowBypass = true): bool;
}
