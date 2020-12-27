<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

/**
 * @internal
 */
class StringPermission implements PermissionTreeNodeInterface
{
    private PermissionCheckerInterface $permissionChecker;

    private string $permissionValue;

    private $serializedPermissions;

    /**
     * StringPermission constructor.
     *
     * @param PermissionCheckerInterface $permissionChecker
     * @param string                     $permissionValue
     * @param array|string               $serializedPermissions
     */
    public function __construct(
        PermissionCheckerInterface $permissionChecker,
        string $permissionValue,
        $serializedPermissions
    ) {
        $this->permissionChecker = $permissionChecker;
        $this->permissionValue = $permissionValue;
        $this->serializedPermissions = $serializedPermissions;
    }

    /**
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
    public function getDebugValues($context = null): array
    {
        return [new DebugPermissionTreeNodeValue(
            $this->getValue($context),
            $this->serializedPermissions
        )];
    }
}
