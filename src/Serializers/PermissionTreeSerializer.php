<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\StringPermission;
use Ordermind\LogicGates\LogicGateInputValueInterface as PermissionTreeNodeInterface;
use Ordermind\LogicGates\LogicGateInterface;
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
        if ($node instanceof LogicGateInterface) {
            return [$node->getName() => array_map([$this, 'serializeNode'], $node->getInputValues())];
        }

        if ($node instanceof StringPermission) {
            return [$node->getPermissionChecker()->getName() => $node->getPermissionValue()];
        }

        return $node->getValue();
    }
}