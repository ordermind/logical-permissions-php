<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use UnexpectedValueException;

/**
 * @internal
 */
class PermissionTreeSerializer
{
    /**
     * Serializes a permission tree into an array structure.
     *
     * @param PermissionTree $permissionTree
     *
     * @return array
     */
    public function serialize(PermissionTree $permissionTree): array
    {
        return (array) $this->serializeNode($permissionTree->getRootNode());
    }

    /**
     * Serializes a permission tree node and its descendants.
     *
     * @param PermissionTreeNodeInterface $node
     *
     * @return array|bool
     *
     * @throws UnexpectedValueException
     */
    private function serializeNode(PermissionTreeNodeInterface $node)
    {
        if ($node instanceof LogicGateNode) {
            return [$node->getName() => array_map([$this, 'serializeNode'], $node->getInputValues())];
        }

        if ($node instanceof StringPermission) {
            return [$node->getPermissionChecker()->getName() => $node->getPermissionValue()];
        }

        return $node->getValue();
    }
}
