<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker;

class ConditionPermissionChecker extends FlagPermissionChecker
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'condition';
    }
}
