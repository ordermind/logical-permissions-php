<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use UnexpectedValueException;

class PermissionTreeNodeSerializer
{
    /**
     * Serializes a permission tree node and its descendants recursively.
     *
     * @param PermissionTreeNodeInterface $node
     *
     * @return array|bool
     *
     * @throws UnexpectedValueException
     */
    public function serialize(PermissionTreeNodeInterface $node)
    {
        if ($node instanceof LogicGateNode) {
            return [$node->getName() => array_map([$this, 'serialize'], $node->getInputValues())];
        }

        if ($node instanceof StringPermission) {
            return [$node->getPermissionChecker()->getName() => $node->getPermissionValue()];
        }

        if ($node instanceof BooleanPermission) {
            return $node->getValue();
        }

        throw new UnexpectedValueException('The serializer does not yet support the node type ' . get_class($node));
    }
}
