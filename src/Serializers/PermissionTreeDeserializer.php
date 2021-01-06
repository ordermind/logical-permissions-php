<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\Factories\LogicGateNodeFactory;
use Ordermind\LogicalPermissions\Helpers\Helper;
use Ordermind\LogicalPermissions\Locators\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use Ordermind\LogicGates\LogicGateEnum;
use TypeError;
use UnexpectedValueException;

/**
 * Normalizes and deserializes a native permission tree structure into a permission tree object.
 */
class PermissionTreeDeserializer
{
    protected PermissionCheckerLocator $locator;
    protected LogicGateNodeFactory $logicGateNodeFactory;

    public function __construct(
        PermissionCheckerLocator $locator,
        LogicGateNodeFactory $logicGateNodeFactory
    ) {
        $this->locator = $locator;
        $this->logicGateNodeFactory = $logicGateNodeFactory;
    }

    /**
     * @param array|string|bool $permissions
     *
     * @throws TypeError
     */
    public function deserialize($permissions): PermissionTree
    {
        if (!is_array($permissions) && !is_string($permissions) && !is_bool($permissions)) {
            throw new TypeError(
                sprintf(
                    'The permissions parameter must be an array or in certain cases a string or boolean. '
                        . 'Evaluated permissions: %s',
                    print_r($permissions, true)
                )
            );
        }

        if (is_array($permissions) && !$permissions) {
            return new PermissionTree(new BooleanPermission(true));
        }

        return new PermissionTree(
            $this->wrapInputValues(LogicGateEnum::OR, $this->parseValue(null, $permissions, null))
        );
    }

    /**
     * @param string|int        $parentKey
     * @param array|string|bool $permissions
     *
     * @return PermissionTreeNodeInterface[]
     *
     * @throws TypeError
     */
    protected function parseValue($parentKey, $permissions, ?string $type): array
    {
        if (is_bool($permissions)) {
            return [$this->parseBoolean($permissions, $type)];
        }

        if (is_string($permissions)) {
            return [$this->parseString($permissions, $type)];
        }

        if (is_array($permissions)) {
            return $this->parseArray($parentKey, $permissions, $type);
        }

        throw new TypeError(
            sprintf(
                'A permission must either be a boolean, a string or an array. Evaluated permissions: %s',
                print_r($permissions, true)
            )
        );
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function parseBoolean(bool $permission, ?string $type): BooleanPermission
    {
        if (null !== $type) {
            throw new UnexpectedValueException(
                'You cannot put a boolean permission as a descendant to a permission type. '
                    . "Existing type: \"$type\". Evaluated permission: $permission"
            );
        }

        return new BooleanPermission($permission);
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function parseString(string $permission, ?string $type): PermissionTreeNodeInterface
    {
        if (empty($permission)) {
            throw new UnexpectedValueException('You cannot use an empty string in a permission tree.');
        }

        if ('TRUE' === strtoupper($permission)) {
            return $this->parseBoolean(true, $type);
        }

        if ('FALSE' === strtoupper($permission)) {
            return $this->parseBoolean(false, $type);
        }

        if (!$type) {
            throw new UnexpectedValueException(
                'A string value cannot be used in a permission tree without having a permission type as an ancestor. '
                    . "Evaluated permissions: \"$permission\""
            );
        }

        $permissionChecker = $this->locator->get($type);

        return new StringPermission($permissionChecker, $permission);
    }

    /**
     * @param string|int $parentKey
     *
     * @return PermissionTreeNodeInterface[]
     */
    protected function parseArray($parentKey, array $permissions, ?string $type): array
    {
        $value = array_map(function ($key, $value) use ($type) {
            return $this->parseArrayElement($key, $value, $type);
        }, array_keys($permissions), $permissions);

        if (count($permissions) > 1 && !LogicGateEnum::isValid($parentKey)) {
            $value = [$this->wrapInputValues(LogicGateEnum::OR, $value)];
        }

        return $value;
    }

    /**
     * @param string|int        $key
     * @param array|string|bool $value
     *
     * @return PermissionTreeNodeInterface[]
     *
     * @throws UnexpectedValueException
     * @throws PermissionTypeNotRegisteredException
     */
    protected function parseArrayElement($key, $value, ?string $type): array
    {
        $permissions = [$key => $value];

        if (is_numeric($key)) {
            return $this->parseValue($key, $value, $type);
        }

        $keyUpper = strtoupper($key);
        if ('NO_BYPASS' === $keyUpper) {
            throw new UnexpectedValueException(
                sprintf(
                    'The NO_BYPASS key must be placed highest in the permission hierarchy. Evaluated permissions: %s',
                    print_r($permissions, true)
                )
            );
        }

        if (LogicGateEnum::isValid($keyUpper)) {
            $inputValues = $this->parseValue($keyUpper, $value, $type);

            return [$this->wrapInputValues($keyUpper, $inputValues)];
        }

        if ('TRUE' === $keyUpper || 'FALSE' === $keyUpper) {
            throw new UnexpectedValueException(
                sprintf(
                    'A boolean permission cannot have children. Evaluated permissions: %s',
                    print_r($permissions, true)
                )
            );
        }

        if (null !== $type) {
            throw new UnexpectedValueException(
                sprintf(
                    'You cannot put a permission type as a descendant to another permission type. '
                        . 'Existing type: "%s". Evaluated permissions: %s',
                    $type,
                    print_r($permissions, true)
                )
            );
        }
        if (!$this->locator->has($key)) {
            throw new PermissionTypeNotRegisteredException("The permission type \"$key\" could not be found.");
        }

        return $this->parseValue($key, $value, $key);
    }

    protected function wrapInputValues(
        string $logicGateName,
        array $inputValues
    ): PermissionTreeNodeInterface {
        $inputValues = Helper::flattenNumericArray($inputValues);

        if (count($inputValues) == 1 && in_array($logicGateName, [LogicGateEnum::AND, LogicGateEnum::OR])) {
            return $inputValues[0];
        }

        return $this->logicGateNodeFactory->createFromEnum(
            new LogicGateEnum($logicGateName),
            ...$inputValues
        );
    }
}
