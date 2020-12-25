<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

class PermissionTreeNodeValue
{
    private bool $internalValue;

    /**
     * @param array|string|bool $permissions
     */
    private $permissions;

    /**
     * @var self[]
     */
    private array $childValues;

    /**
     * @param array|string|bool $permissions
     */
    public function __construct(bool $internalValue, $permissions, self ...$childValues)
    {
        $this->internalValue = $internalValue;
        $this->permissions = $permissions;
        $this->childValues = $childValues;
    }

    public function getInternalValue(): bool
    {
        return $this->internalValue;
    }

    /**
     * @return array|string|bool $permissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getChildValues(): array
    {
        return $this->childValues;
    }
}
