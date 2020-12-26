<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use TypeError;

/**
 * Converts a native permission tree including NO_BYPASS part into a validated and typed permission tree.
 */
class FullPermissionTreeDeserializer
{
    private PermissionTreeDeserializer $treeDeserializer;

    public function __construct(PermissionTreeDeserializer $treeDeserializer)
    {
        $this->treeDeserializer = $treeDeserializer;
    }

    /**
     * Deserializes a native permission tree into a permission tree object.
     *
     * @param array|string|bool $permissions
     *
     * @return FullPermissionTree
     *
     * @throws TypeError
     */
    public function deserialize($permissions): FullPermissionTree
    {
        if (!is_array($permissions) && !is_string($permissions) && !is_bool($permissions)) {
            throw new TypeError(
                sprintf(
                    'The permissions parameter must be an array or in certain cases a string or boolean. '
                        . 'Evaluated permissions: %s',
                    print_r($permissions, true)
                )
            );
        }

        $noBypassTree = null;

        if (is_array($permissions)) {
            if (array_key_exists('no_bypass', $permissions)) {
                $permissions['NO_BYPASS'] = $permissions['no_bypass'];
                unset($permissions['no_bypass']);
            }

            if (array_key_exists('NO_BYPASS', $permissions)) {
                $noBypassTree = $this->treeDeserializer->deserialize($permissions['NO_BYPASS']);
                unset($permissions['NO_BYPASS']);
            }
        }

        $mainTree = $this->treeDeserializer->deserialize($permissions);

        return new FullPermissionTree($permissions, $mainTree, $noBypassTree);
    }
}
