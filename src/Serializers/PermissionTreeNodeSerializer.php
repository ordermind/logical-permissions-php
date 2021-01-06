<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;

/**
 * Serializes a permission tree node and its descendants recursively.
 */
class PermissionTreeNodeSerializer
{
    /**
     * @return array|bool
     *
     * @throws InvalidArgumentException
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

        throw new InvalidArgumentException('The serializer does not yet support the node type ' . get_class($node));
    }
}
