<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

use Ordermind\LogicalPermissions\Helpers\Helper;
use Ordermind\LogicGates\LogicGateInterface;

class LogicGateNode implements PermissionTreeNodeInterface, LogicGateInterface
{
    private LogicGateInterface $logicGate;

    /**
     * @var array|string|bool
     */
    private $serializedPermissions;

    /**
     * @param array|string|bool $serializedPermissions
     */
    public function __construct(LogicGateInterface $logicGate, $serializedPermissions)
    {
        $this->logicGate = $logicGate;
        $this->serializedPermissions = $serializedPermissions;
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

    /**
     * {@inheritDoc}
     */
    public function getDebugValues($context = null): array
    {
        $myDebugValue = new DebugPermissionTreeNodeValue(
            $this->getValue($context),
            $this->serializedPermissions
        );

        $descendantDebugValues = Helper::flattenNumericArray(
            array_map(
                function (PermissionTreeNodeInterface $childNode): array {
                    return $childNode->getDebugValues();
                },
                $this->logicGate->getInputValues()
            )
        );

        return array_merge([$myDebugValue], $descendantDebugValues);
    }
}
