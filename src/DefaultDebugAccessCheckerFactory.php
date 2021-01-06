<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\Debug\AccessChecker\DebugAccessChecker;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeEvaluator;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeSerializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeNodeSerializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeSerializer;

/**
 * Factory to help create a default instance of a debug access checker.
 */
class DefaultDebugAccessCheckerFactory
{
    public function create(?BypassAccessCheckerInterface $bypassAccessChecker = null): DebugAccessChecker
    {
        $nodeSerializer = new PermissionTreeNodeSerializer();

        return new DebugAccessChecker(
            new BypassAccessCheckerDecorator($bypassAccessChecker),
            new DebugPermissionTreeEvaluator($nodeSerializer),
            new FullPermissionTreeSerializer(new PermissionTreeSerializer($nodeSerializer))
        );
    }
}
