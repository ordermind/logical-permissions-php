<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;

/**
 * @internal
 */
class PermissionTree
{
    private PermissionTreeNodeInterface $rootNode;

    /**
     * PermissionTree constructor.
     *
     * @param PermissionTreeNodeInterface $rootNode
     */
    public function __construct(PermissionTreeNodeInterface $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    /**
     * Gets the root node of the permission tree.
     *
     * @return PermissionTreeNodeInterface
     */
    public function getRootNode(): PermissionTreeNodeInterface
    {
        return $this->rootNode;
    }

    /**
     * Evaluates the permission tree and returns the resulting value.
     *
     * @param array|object|null $context
     *
     * @return bool
     */
    public function evaluate($context = null): bool
    {
        return $this->rootNode->getValue($context);
    }
}
