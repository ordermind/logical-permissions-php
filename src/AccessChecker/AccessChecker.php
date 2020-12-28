<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use TypeError;

/**
 * @internal
 */
class AccessChecker implements AccessCheckerInterface
{
    protected BypassAccessCheckerDecorator $bypassAccessCheckerDecorator;

    public function __construct(BypassAccessCheckerDecorator $bypassAccessCheckerDecorator)
    {
        $this->bypassAccessCheckerDecorator = $bypassAccessCheckerDecorator;
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess(FullPermissionTree $fullPermissionTree, $context = null, bool $allowBypass = true): bool
    {
        if (!is_null($context) && !is_array($context) && !is_object($context)) {
            throw new TypeError('The context parameter must be an array or object.');
        }

        $allowBypass = $this->bypassAccessCheckerDecorator->isBypassAllowed(
            $fullPermissionTree,
            $context,
            $allowBypass
        );

        if ($allowBypass && $this->bypassAccessCheckerDecorator->checkBypassAccess($context)) {
            return true;
        }

        return $fullPermissionTree->getMainTree()->evaluate($context);
    }
}
