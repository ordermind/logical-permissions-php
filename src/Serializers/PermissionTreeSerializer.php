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
        $permissions = $this->serializeNode($permissionTree->getRootNode());

        if (!is_array($permissions)) {
            return [$permissions];
        }

        return $permissions;
    }

    /**
     * Serializes a permission tree node and its descendants.
     *
     * @param PermissionTreeNodeInterface $node
     *
     * @return mixed
     *
     * @throws UnexpectedValueException
     */
    private function serializeNode(PermissionTreeNodeInterface $node)
    {
        if ($node instanceof LogicGateInterface) {
            return [$node->getName() => array_map(function (PermissionTreeNodeInterface $inputValue) {
                return $this->serializeNode($inputValue);
            }, $node->getInputValues())];
        }

        if ($node instanceof StringPermission) {
            return [$node->getPermissionChecker()->getName() => $node->getPermissionValue()];
        }

        return $node->getValue();
    }
}
