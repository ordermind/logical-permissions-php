<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Locators;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyRegisteredException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Ordermind\LogicGates\LogicGateEnum;

class PermissionCheckerLocator
{
    /**
     * @var PermissionCheckerInterface[]
     */
    protected array $permissionCheckers = [];

    public function __construct(PermissionCheckerInterface ...$permissionCheckers)
    {
        foreach ($permissionCheckers as $permissionChecker) {
            $this->add($permissionChecker);
        }
    }

    /**
     * @param iterable<PermissionCheckerInterface> $permissionCheckers
     */
    public static function fromIterable(iterable $permissionCheckers): self
    {
        return new self(...$permissionCheckers);
    }

    /**
     * Registers a permission checker.
     *
     * @param PermissionCheckerInterface $permissionChecker
     * @param bool                       $overwriteIfExists (optional) If the permission checker is already registered,
     *                                                      it will be overwritten if this parameter is set to `true`.
     *                                                      If it is set to `false`,
     *                                                      PermissionTypeAlreadyRegisteredException will be thrown.
     *                                                      Default value is `false`.
     */
    public function add(
        PermissionCheckerInterface $permissionChecker,
        bool $overwriteIfExists = false
    ): void {
        $this->validatePermissionType($permissionChecker, $overwriteIfExists);

        $this->permissionCheckers[$permissionChecker->getName()] = $permissionChecker;
    }

    /**
     * Unregisters a permission checker by name. If the permission checker cannot be found, nothing happens.
     *
     * @throws InvalidArgumentException
     */
    public function remove(string $name): void
    {
        if (!$name) {
            throw new InvalidArgumentException('The name must not be empty.');
        }

        unset($this->permissionCheckers[$name]);
    }

    /**
     * Checks if a permission checker name is registered.
     *
     * @throws InvalidArgumentException
     */
    public function has(string $name): bool
    {
        if (!$name) {
            throw new InvalidArgumentException('The name must not be empty.');
        }

        return isset($this->permissionCheckers[$name]);
    }

    /**
     * Gets a permission checker by name. If the permission checker is not registered,
     * PermissionTypeNotRegisteredException is thrown.
     *
     * @throws PermissionTypeNotRegisteredException
     */
    public function get(string $name): PermissionCheckerInterface
    {
        if (!$this->has($name)) {
            throw new PermissionTypeNotRegisteredException("The permission type \"$name\" has not been registered.");
        }

        return $this->permissionCheckers[$name];
    }

    /**
     * Gets all registered permission checkers as an array, keyed by permission type.
     *
     * @return PermissionCheckerInterface[]
     */
    public function all(): array
    {
        return $this->permissionCheckers;
    }

    /**
     * Gets all keys that may be used in a permission tree.
     *
     * @return string[]
     */
    public function getValidPermissionTreeKeys(): array
    {
        return array_merge(
            $this->getReservedKeys(),
            $this->getRegisteredKeys()
        );
    }

    /**
     * @throws InvalidPermissionTypeException
     * @throws PermissionTypeAlreadyRegisteredException
     */
    protected function validatePermissionType(PermissionCheckerInterface $permissionChecker, bool $allowExisting): void
    {
        $name = $permissionChecker->getName();

        if (!$name) {
            throw new InvalidPermissionTypeException(
                sprintf('The name of the permission checker %s must not be empty.', get_class($permissionChecker))
            );
        }
        if (in_array(strtoupper($name), $reservedKeys = $this->getReservedKeys())) {
            throw new InvalidPermissionTypeException(
                sprintf(
                    'The permission checker %s has the illegal name "%s". '
                        . 'It must not be one of the following values: [%s]',
                    get_class($permissionChecker),
                    $name,
                    implode(', ', $reservedKeys)
                )
            );
        }

        if ($this->has($name) && !$allowExisting) {
            throw new PermissionTypeAlreadyRegisteredException("The permission type \"$name\" is already registered");
        }
    }

    protected function getReservedKeys(): array
    {
        return array_merge(LogicGateEnum::keys(), ['NO_BYPASS', 'TRUE', 'FALSE']);
    }

    protected function getRegisteredKeys(): array
    {
        return array_keys($this->permissionCheckers);
    }
}
