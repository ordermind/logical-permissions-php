<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

interface PermissionTreeNodeInterface
{
    /**
     * Gets the value.
     *
     * @param mixed $context
     *
     * @return PermissionTreeNodeValue
     */
    public function getValue($context = null): PermissionTreeNodeValue;
}
