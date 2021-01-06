<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeEvaluator;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeSerializer;

class DebugAccessChecker
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

    /**
     * Checks access for a permission tree and returns the result together with debug information.
     *
     * @param FullPermissionTree $fullPermissionTree The permission tree to be evaluated
     * @param mixed              $context            (optional) A context that could for example contain the evaluated
     *                                               user and model. Default value is `null`.
     * @param bool               $allowBypass        (optional) Determines whether bypassing access should be allowed.
     *                                               Default value is `true`.
     *
     * @return DebugAccessCheckerResult
     */
    public function checkAccess(
        FullPermissionTree $fullPermissionTree,
        $context = null,
        bool $allowBypass = true
    ): DebugAccessCheckerResult {
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
