<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions;

/**
 * Definition of a permission checker.
 */
interface PermissionCheckerInterface
{
    /**
     * Gets the name of the permission that this class checks, for example "role".
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Checks if access should be granted for this permission value in a given context.
     *
     * @param string $value   The value to check, for example "admin" if the permission name is
     *                        "role". The value will always be a single string even if for example
     *                        multiple roles are accepted. In that case this method will be called once
     *                        for each role that is to be evaluated.
     * @param mixed  $context The context for evaluating the permission
     *
     * @return bool TRUE if access is granted or FALSE if access is not granted
     */
    public function checkPermission(string $value, $context): bool;
}
