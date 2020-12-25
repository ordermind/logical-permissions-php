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

    /**
     * Creates a logic gate node from a logic gate enum.
     *
     * @param array|string|bool $debugPermissions
     */
    public function createFromEnum(
        LogicGateEnum $gateEnum,
        $debugPermissions,
        PermissionTreeNodeInterface ...$inputValues
    ): LogicGateNode {
        return new LogicGateNode(
            $this->logicGateFactory->createFromEnum($gateEnum, ...$inputValues),
            $debugPermissions
        );
    }
}
