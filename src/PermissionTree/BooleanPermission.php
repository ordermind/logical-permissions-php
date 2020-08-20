<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

use Ordermind\LogicGates\LogicGateInputValueInterface as PermissionTreeNodeInterface;

/**
 * @internal
 */
class BooleanPermission implements PermissionTreeNodeInterface
{
    /**
     * @var bool
     */
    private $value;

    /**
     * BooleanPermission constructor.
     *
     * @param bool $value
     */
    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    /**
     * @{inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($context = null): bool
    {
        return $this->value;
    }
}
