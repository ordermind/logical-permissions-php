<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Ordermind\LogicGates\LogicGateInputValueInterface as PermissionTreeNodeInterface;

/**
 * @internal
 */
class StringPermission implements PermissionTreeNodeInterface
{
    /**
     * @var PermissionCheckerInterface
     */
    private $permissionChecker;

    /**
     * @var string
     */
    private $permissionValue;

    /**
     * StringPermission constructor.
     *
     * @param PermissionCheckerInterface $permissionChecker
     * @param string                     $permissionValue
     */
    public function __construct(PermissionCheckerInterface $permissionChecker, string $permissionValue)
    {
        $this->permissionChecker = $permissionChecker;
        $this->permissionValue = $permissionValue;
    }

    /**
     * @{inheritDoc}
     */
    public function getValue($context = null): bool
    {
        return $this->permissionChecker->checkPermission($this->permissionValue, $context);
    }

    /**
     * Gets the permission checker.
     *
     * @return PermissionCheckerInterface
     */
    public function getPermissionChecker(): PermissionCheckerInterface
    {
        return $this->permissionChecker;
    }

    /**
     * Gets the permission value.
     *
     * @return string
     */
    public function getPermissionValue(): string
    {
        return $this->permissionValue;
    }
}
