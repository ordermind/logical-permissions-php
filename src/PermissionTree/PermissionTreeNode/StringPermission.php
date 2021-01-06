<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class StringPermission implements PermissionTreeNodeInterface
{
    protected PermissionCheckerInterface $permissionChecker;

    protected string $permissionValue;

    public function __construct(
        PermissionCheckerInterface $permissionChecker,
        string $permissionValue
    ) {
        $this->permissionChecker = $permissionChecker;
        $this->permissionValue = $permissionValue;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($context = null): bool
    {
        return $this->permissionChecker->checkPermission($this->permissionValue, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(): array
    {
        return [];
    }

    public function getPermissionChecker(): PermissionCheckerInterface
    {
        return $this->permissionChecker;
    }

    public function getPermissionValue(): string
    {
        return $this->permissionValue;
    }
}
