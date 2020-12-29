<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;

class UnknownNodeType implements PermissionTreeNodeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getChildren(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($context = null): bool
    {
        return true;
    }
}
