<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

/**
 * @internal
 */
class BooleanPermission implements PermissionTreeNodeInterface
{
    private bool $value;

    /**
     * @var bool|string
     */
    private $debugPermissions;

    /**
     * BooleanPermission constructor.
     *
     * @param bool        $value
     * @param bool|string $debugPermissions
     */
    public function __construct(bool $value, $debugPermissions)
    {
        $this->value = $value;
        $this->debugPermissions = $debugPermissions;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($context = null): bool
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getDebugValues($context = null): array
    {
        return [new PermissionTreeNodeDebugValue(
            $this->getValue($context),
            $this->debugPermissions
        )];
    }
}
