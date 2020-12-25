<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicGates\LogicGateInputValueInterface;

interface PermissionTreeNodeInterface extends LogicGateInputValueInterface
{
    /**
     * Gets the value together with debug information.
     *
     * @param mixed $context
     *
     * @return PermissionTreeNodeDebugValue
     */
    public function getDebugValue($context = null): PermissionTreeNodeDebugValue;
}
