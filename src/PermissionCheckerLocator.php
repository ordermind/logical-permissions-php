<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions;

use Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyRegisteredException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicGates\LogicGateEnum;
use UnexpectedValueException;

/**
 * Service locator for permission checkers.
 */
class PermissionCheckerLocator implements PermissionCheckerLocatorInterface
{
    /**
     * @var PermissionCheckerInterface[]
     */
    protected array $permissionCheckers = [];

    /**
     * PermissionCheckerLocator constructor.
     *
     * @param iterable|PermissionCheckerInterface[] $permissionCheckers
     */
    public function __construct(iterable $permissionCheckers = [])
    {
        foreach ($permissionCheckers as $permissionChecker) {
            $this->add($permissionChecker);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function add(
        PermissionCheckerInterface $permissionChecker,
        bool $overwriteIfExists = false
    ): PermissionCheckerLocatorInterface {
        $this->validatePermissionType($permissionChecker, $overwriteIfExists);

        $this->permissionCheckers[$permissionChecker->getName()] = $permissionChecker;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $name): PermissionCheckerLocatorInterface
    {
        if (!$name) {
            throw new UnexpectedValueException('The name must not be empty.');
        }

        unset($this->permissionCheckers[$name]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $name): bool
    {
        if (!$name) {
            throw new UnexpectedValueException('The name must not be empty.');
        }

        return isset($this->permissionCheckers[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $name): PermissionCheckerInterface
    {
        if (!$this->has($name)) {
            throw new PermissionTypeNotRegisteredException("The permission type \"$name\" has not been registered.");
        }

        return $this->permissionCheckers[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->permissionCheckers;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidPermissionTreeKeys(): array
    {
        return array_merge(
            $this->getReservedKeys(),
            $this->getKeys()
        );
    }

    /**
     * Validates a permission checker.
     *
     * @internal
     *
     * @param PermissionCheckerInterface $permissionChecker
     * @param bool                       $allowExisting
     *
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

    /**
     * Gets reserved permission keys.
     *
     * @internal
     *
     * @return array
     */
    protected function getReservedKeys(): array
    {
        return array_merge(LogicGateEnum::keys(), ['NO_BYPASS', 'TRUE', 'FALSE']);
    }

    /**
     * Gets all keys within the collection.
     *
     * @internal
     *
     * @return array
     */
    protected function getKeys(): array
    {
        return array_keys($this->permissionCheckers);
    }
}
