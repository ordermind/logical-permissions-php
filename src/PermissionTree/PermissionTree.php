<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;

class PermissionTree
{
    protected PermissionTreeNodeInterface $rootNode;

    public function __construct(PermissionTreeNodeInterface $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    public function getRootNode(): PermissionTreeNodeInterface
    {
        return $this->rootNode;
    }

    /**
     * @param mixed $context
     */
    public function evaluate($context = null): bool
    {
        return $this->rootNode->getValue($context);
    }
}
