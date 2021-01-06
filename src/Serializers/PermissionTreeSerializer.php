<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;

/**
 * Serializes a permission tree into an array structure.
 */
class PermissionTreeSerializer
{
    protected PermissionTreeNodeSerializer $nodeSerializer;

    public function __construct(PermissionTreeNodeSerializer $nodeSerializer)
    {
        $this->nodeSerializer = $nodeSerializer;
    }

    public function serialize(PermissionTree $permissionTree): array
    {
        return (array) $this->nodeSerializer->serialize($permissionTree->getRootNode());
    }
}
