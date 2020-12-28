<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\PermissionTree;

/**
 * DTO that holds the evaluated value and debug information of a permission tree node.
 */
class DebugPermissionTreeNodeValue
{
    protected bool $resultValue;

    /**
     * @param array|string|bool $permissions
     */
    protected $permissions;

    /**
     * @param array|string|bool $permissions
     */
    public function __construct(bool $resultValue, $permissions)
    {
        $this->resultValue = $resultValue;
        $this->permissions = $permissions;
    }

    public function getResultValue(): bool
    {
        return $this->resultValue;
    }

    /**
     * @return array|string|bool $permissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
