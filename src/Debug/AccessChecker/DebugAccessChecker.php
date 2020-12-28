<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeEvaluator;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeSerializer;
use TypeError;

class DebugAccessChecker implements DebugAccessCheckerInterface
{
    protected BypassAccessCheckerDecorator $bypassAccessCheckerDecorator;

    protected DebugPermissionTreeEvaluator $debugTreeEvaluator;

    protected FullPermissionTreeSerializer $fullPermissionTreeSerializer;

    public function __construct(
        BypassAccessCheckerDecorator $bypassAccessCheckerDecorator,
        DebugPermissionTreeEvaluator $debugTreeEvaluator,
        FullPermissionTreeSerializer $fullPermissionTreeSerializer
    ) {
        $this->bypassAccessCheckerDecorator = $bypassAccessCheckerDecorator;
        $this->debugTreeEvaluator = $debugTreeEvaluator;
        $this->fullPermissionTreeSerializer = $fullPermissionTreeSerializer;
    }

    public function checkAccess(
        FullPermissionTree $fullPermissionTree,
        $context = null,
        bool $allowBypass = true
    ): DebugAccessCheckerResult {
        if (!is_null($context) && !is_array($context) && !is_object($context)) {
            throw new TypeError('The context parameter must be an array or object.');
        }

        $mainTreeResult = $this->debugTreeEvaluator->evaluate($fullPermissionTree->getMainTree(), $context);
        $noBypassTreeResult = null;
        if ($fullPermissionTree->hasNoBypassTree()) {
            $noBypassTreeResult = $this->debugTreeEvaluator->evaluate($fullPermissionTree->getNoBypassTree(), $context);
        }

        $allowBypass = $this->bypassAccessCheckerDecorator->isBypassAllowed(
            $fullPermissionTree,
            $context,
            $allowBypass
        );

        $hasBypassedAccess = $allowBypass && $this->bypassAccessCheckerDecorator->checkBypassAccess($context);

        return new DebugAccessCheckerResult(
            $hasBypassedAccess,
            $mainTreeResult,
            $noBypassTreeResult,
            $this->fullPermissionTreeSerializer->serialize($fullPermissionTree),
            $context
        );
    }
}
