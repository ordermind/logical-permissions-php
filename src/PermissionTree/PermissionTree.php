<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

use Ordermind\LogicGates\LogicGateInputValueInterface as PermissionTreeNodeInterface;

/**
 * @internal
 */
class PermissionTree
{
    /**
     * @var PermissionTreeNodeInterface
     */
    private $rootNode;

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
     * Resolves the permission tree and returns the resulting value.
     *
     * @param array|object|null $context
     *
     * @return bool
     */
    public function resolve($context = null): bool
    {
        return $this->rootNode->getValue($context);
    }
}
