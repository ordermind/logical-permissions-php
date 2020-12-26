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
    private $serializedPermissions;

    /**
     * BooleanPermission constructor.
     *
     * @param bool        $value
     * @param bool|string $serializedPermissions
     */
    public function __construct(bool $value, $serializedPermissions)
    {
        $this->value = $value;
        $this->serializedPermissions = $serializedPermissions;
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
        return [new DebugPermissionTreeNodeValue(
            $this->getValue($context),
            $this->serializedPermissions
        )];
    }
}
