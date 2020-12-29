<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\AccessChecker;

use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeResult;

class DebugAccessCheckerResult
{
    protected bool $hasBypassedAccess;

    protected DebugPermissionTreeResult $mainTreeResult;

    protected ?DebugPermissionTreeResult $noBypassTreeResult;

    protected array $normalizedPermissions;

    /**
     * @var array|object|null
     */
    protected $context;

    /**
     * @param array|object|null $context
     */
    public function __construct(
        bool $hasBypassedAccess,
        DebugPermissionTreeResult $mainTreeResult,
        ?DebugPermissionTreeResult $noBypassTreeResult,
        array $normalizedPermissions,
        $context
    ) {
        $this->hasBypassedAccess = $hasBypassedAccess;
        $this->mainTreeResult = $mainTreeResult;
        $this->noBypassTreeResult = $noBypassTreeResult;
        $this->normalizedPermissions = $normalizedPermissions;
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

    public function getNormalizedPermissions(): array
    {
        return $this->normalizedPermissions;
    }

    /**
     * @return array|object|null
     */
    public function getContext()
    {
        return $this->context;
    }
}
