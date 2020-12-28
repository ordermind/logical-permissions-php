<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicGates\LogicGateInputValueInterface;

interface PermissionTreeNodeInterface extends LogicGateInputValueInterface
{
    /**
     * Gets all children of this node.
     *
     * @return PermissionTreeNodeInterface[]
     */
    public function getChildren(): array;
}
