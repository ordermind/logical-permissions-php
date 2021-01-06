<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

class BooleanPermission implements PermissionTreeNodeInterface
{
    protected bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($context = null): bool
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(): array
    {
        return [];
    }
}
