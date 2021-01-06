<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;

/**
 * Factory to help create a default instance of an access checker.
 */
class DefaultAccessCheckerFactory
{
    public function create(?BypassAccessCheckerInterface $bypassAccessChecker = null): AccessChecker
    {
        return new AccessChecker(new BypassAccessCheckerDecorator($bypassAccessChecker));
    }
}
