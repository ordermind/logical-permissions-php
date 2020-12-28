<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicGates\LogicGateInterface;

class LogicGateNode implements PermissionTreeNodeInterface, LogicGateInterface
{
    private LogicGateInterface $logicGate;

    public function __construct(LogicGateInterface $logicGate)
    {
        $this->logicGate = $logicGate;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->logicGate->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getInputValues(): array
    {
        return $this->logicGate->getInputValues();
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(): array
    {
        /**
         * @var PermissionTreeNodeInterface[] $children
         */
        $children = $this->getInputValues();

        return $children;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($context = null): bool
    {
        return $this->logicGate->execute($context);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($context = null): bool
    {
        return $this->logicGate->getValue($context);
    }
}
