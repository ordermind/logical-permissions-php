<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicGates\LogicGateInputValueInterface;

interface PermissionTreeNodeInterface extends LogicGateInputValueInterface
{
    /**
     * Gets the evaluated value together with debug information for this node and all of its descendants in the tree.
     *
     * @param mixed $context
     *
     * @return DebugPermissionTreeNodeValue[]
     */
    public function getDebugValues($context = null): array;
}
