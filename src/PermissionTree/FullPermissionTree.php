<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree;

/**
 * Represents a full permission tree, including an optional NO_BYPASS part.
 */
class FullPermissionTree
{
    protected PermissionTree $mainTree;

    protected ?PermissionTree $noBypassTree;

    public function __construct(PermissionTree $mainTree, ?PermissionTree $noBypassTree = null)
    {
        $this->mainTree = $mainTree;
        $this->noBypassTree = $noBypassTree;
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
