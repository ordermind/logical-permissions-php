<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeNodeSerializer;

class DebugPermissionTreeEvaluator
{
    protected PermissionTreeNodeSerializer $nodeSerializer;

    public function __construct(PermissionTreeNodeSerializer $nodeSerializer)
    {
        $this->nodeSerializer = $nodeSerializer;
    }

    /**
     * Evaluates the permission tree and returns the resulting value, together with debug information for each node.
     *
     * @param mixed $context
     */
    public function evaluate(PermissionTree $permissionTree, $context = null): DebugPermissionTreeResult
    {
        return new DebugPermissionTreeResult(
            $permissionTree->getRootNode()->getValue($context),
            ...$this->evaluateNodeRecursive($permissionTree->getRootNode(), $context)
        );
    }

    /**
     * @return DebugPermissionTreeNodeValue[]
     */
    protected function evaluateNodeRecursive(PermissionTreeNodeInterface $node, $context = null): array
    {
        return array_merge(
            [new DebugPermissionTreeNodeValue($node->getValue($context), $this->nodeSerializer->serialize($node))],
            ...array_map(function (PermissionTreeNodeInterface $childNode) use ($context): array {
                return $this->evaluateNodeRecursive($childNode, $context);
            }, $node->getChildren())
        );
    }
}
