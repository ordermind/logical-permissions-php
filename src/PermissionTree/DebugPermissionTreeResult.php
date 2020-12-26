<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\DebugPermissionTreeNodeValue;

/**
 * DTO that holds the evaluated permission tree value together with debug information for all the nodes.
 */
class DebugPermissionTreeResult
{
    protected bool $value;

    /**
     * @var DebugPermissionTreeNodeValue[]
     */
    protected array $debugValues;

    public function __construct(bool $value, DebugPermissionTreeNodeValue ...$debugValues)
    {
        $this->value = $value;
        $this->debugValues = $debugValues;
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    /**
     * @return DebugPermissionTreeNodeValue[]
     */
    public function getDebugValues(): array
    {
        return $this->debugValues;
    }
}
