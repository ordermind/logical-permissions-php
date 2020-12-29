<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\PermissionTree;

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
