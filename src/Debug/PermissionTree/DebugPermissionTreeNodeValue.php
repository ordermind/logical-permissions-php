<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Debug\PermissionTree;

class DebugPermissionTreeNodeValue
{
    protected bool $evaluatedValue;

    /**
     * The serialized and normalized permission tree for the evaluated node and its descendants.
     *
     * @var array|bool
     */
    protected $normalizedPermissions;

    /**
     * @param array|bool $normalizedPermissions
     */
    public function __construct(bool $evaluatedValue, $normalizedPermissions)
    {
        $this->evaluatedValue = $evaluatedValue;
        $this->normalizedPermissions = $normalizedPermissions;
    }

    public function getEvaluatedValue(): bool
    {
        return $this->evaluatedValue;
    }

    /**
     * @return array|bool
     */
    public function getNormalizedPermissions()
    {
        return $this->normalizedPermissions;
    }
}
