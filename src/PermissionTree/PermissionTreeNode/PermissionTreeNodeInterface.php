<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicGates\LogicGateInputValueInterface;

interface PermissionTreeNodeInterface extends LogicGateInputValueInterface
{
    /**
     * @return PermissionTreeNodeInterface[]
     */
    public function getChildren(): array;
}
