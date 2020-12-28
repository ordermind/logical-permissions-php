<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Factories;

use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicGates\LogicGateEnum;
use Ordermind\LogicGates\LogicGateFactory;

class LogicGateNodeFactory
{
    private LogicGateFactory $logicGateFactory;

    public function __construct(LogicGateFactory $logicGateFactory)
    {
        $this->logicGateFactory = $logicGateFactory;
    }

    public function createFromEnum(
        LogicGateEnum $gateEnum,
        PermissionTreeNodeInterface ...$inputValues
    ): LogicGateNode {
        return new LogicGateNode($this->logicGateFactory->createFromEnum($gateEnum, ...$inputValues));
    }
}
