<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\DebugPermissionTreeNodeValue;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;

/**
 * @internal
 */
class PermissionTree
{
    /**
     * @var array|string|bool
     */
    private $serializedPermissions;

    private PermissionTreeNodeInterface $rootNode;

    /**
     * PermissionTree constructor.
     *
     * @param array|string|bool           $serializedPermissions
     * @param PermissionTreeNodeInterface $rootNode
     */
    public function __construct($serializedPermissions, PermissionTreeNodeInterface $rootNode)
    {
        $this->serializedPermissions = $serializedPermissions;
        $this->rootNode = $rootNode;
    }

    /**
     * @return array|string|bool
     */
    public function getSerializedPermissions()
    {
        return $this->serializedPermissions;
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

    /**
     * Evaluates the permission tree and returns the resulting value, together with debug information for each node.
     *
     * @param array|object|null $context
     *
     * @return DebugPermissionTreeResult
     */
    public function evaluateWithDebug($context = null): DebugPermissionTreeResult
    {
        $debugValues = $this->rootNode->getDebugValues($context);
        $resultValue = $debugValues[0]->getResultValue();

        if ($debugValues[0]->getPermissions() !== $this->getSerializedPermissions()) {
            array_unshift(
                $debugValues,
                new DebugPermissionTreeNodeValue($resultValue, $this->getSerializedPermissions())
            );
        }

        return new DebugPermissionTreeResult($resultValue, ...$debugValues);
    }
}
