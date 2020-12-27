<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

/**
 * Represents a full permission tree, including an optional NO_BYPASS part.
 */
class FullPermissionTree
{
    /**
     * @var array|string|bool
     */
    private $serializedPermissions;

    private PermissionTree $mainTree;

    private ?PermissionTree $noBypassTree;

    /**
     * FullPermissionTree constructor.
     *
     * @param array|string|bool   $serializedPermissions
     * @param PermissionTree      $mainTree
     * @param PermissionTree|null $noBypassTree
     */
    public function __construct($serializedPermissions, PermissionTree $mainTree, ?PermissionTree $noBypassTree = null)
    {
        $this->serializedPermissions = $serializedPermissions;
        $this->mainTree = $mainTree;
        $this->noBypassTree = $noBypassTree;
    }

    /**
     * @return array|string|bool
     */
    public function getSerializedPermissions()
    {
        return $this->serializedPermissions;
    }

    public function getMainTree(): PermissionTree
    {
        return $this->mainTree;
    }

    public function hasNoBypassTree(): bool
    {
        return !is_null($this->getNoBypassTree());
    }

    public function getNoBypassTree(): ?PermissionTree
    {
        return $this->noBypassTree;
    }
}
