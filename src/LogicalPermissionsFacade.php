<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\AccessChecker\AccessCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Validators\NoBypassValidator;
use Ordermind\LogicGates\LogicGateFactory;

class LogicalPermissionsFacade implements AccessCheckerInterface
{
    /**
     * @var AccessCheckerInterface
     */
    protected $accessChecker;

    /**
     * LogicalPermissionsFacade constructor.
     *
     * @param PermissionCheckerLocatorInterface|null $locator
     * @param BypassAccessCheckerInterface|null      $bypassAccessChecker
     */
    public function __construct(
        ?PermissionCheckerLocatorInterface $locator = null,
        ?BypassAccessCheckerInterface $bypassAccessChecker = null
    ) {
        if (null === $locator) {
            $locator = new PermissionCheckerLocator();
        }

        $treeDeserializer = new PermissionTreeDeserializer($locator, new LogicGateFactory());

        $this->accessChecker = new AccessChecker($treeDeserializer, new NoBypassValidator(), $bypassAccessChecker);
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess(RawPermissionTree $rawPermissionTree, $context = null, bool $allowBypass = true): bool
    {
        return $this->accessChecker->checkAccess($rawPermissionTree, $context, $allowBypass);
    }
}
