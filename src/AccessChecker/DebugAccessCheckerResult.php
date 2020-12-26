<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\PermissionTree\DebugPermissionTreeResult;

/**
 * Value object that holds the access checker result together with debug information for both the main tree and the no
 * bypass tree.
 */
class DebugAccessCheckerResult
{
    protected bool $hasBypassedAccess;

    protected DebugPermissionTreeResult $mainTreeResult;

    protected ?DebugPermissionTreeResult $noBypassTreeResult;

    /**
     * @var array|string|bool
     */
    protected $serializedPermissions;

    /**
     * @var array|object|null
     */
    protected $context;

    /**
     * @param array|string|bool $serializedPermissions
     * @param array|object|null $context
     */
    public function __construct(
        bool $hasBypassedAccess,
        DebugPermissionTreeResult $mainTreeResult,
        ?DebugPermissionTreeResult $noBypassTreeResult,
        $serializedPermissions,
        $context
    ) {
        $this->hasBypassedAccess = $hasBypassedAccess;
        $this->mainTreeResult = $mainTreeResult;
        $this->noBypassTreeResult = $noBypassTreeResult;
        $this->serializedPermissions = $serializedPermissions;
        $this->context = $context;
    }

    public function getAccess(): bool
    {
        return $this->hasBypassedAccess || $this->getMainTreeResult()->getValue();
    }

    public function getMainTreeResult(): DebugPermissionTreeResult
    {
        return $this->mainTreeResult;
    }

    public function getNoBypassTreeResult(): ?DebugPermissionTreeResult
    {
        return $this->noBypassTreeResult;
    }

    /**
     * @return array|string|bool
     */
    public function getSerializedPermissions()
    {
        return $this->serializedPermissions;
    }

    /**
     * @return array|object|null
     */
    public function getContext()
    {
        return $this->context;
    }
}
