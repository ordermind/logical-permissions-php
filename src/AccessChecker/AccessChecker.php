<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\AccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Validators\NoBypassValidator;
use TypeError;

/**
 * @internal
 */
class AccessChecker implements AccessCheckerInterface
{
    /**
     * @var PermissionTreeDeserializer
     */
    protected $deserializer;

    /**
     * @var NoBypassValidator
     */
    protected $noBypassValidator;

    /**
     * @var BypassAccessCheckerInterface|null
     */
    protected $bypassAccessChecker;

    /**
     * AccessChecker constructor.
     *
     * @param PermissionTreeDeserializer        $deserializer
     * @param NoBypassValidator                 $noBypassValidator
     * @param BypassAccessCheckerInterface|null $bypassAccessChecker
     */
    public function __construct(
        PermissionTreeDeserializer $deserializer,
        NoBypassValidator $noBypassValidator,
        ?BypassAccessCheckerInterface $bypassAccessChecker = null
    ) {
        $this->deserializer = $deserializer;
        $this->noBypassValidator = $noBypassValidator;
        $this->bypassAccessChecker = $bypassAccessChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess(RawPermissionTree $rawPermissionTree, $context = null, bool $allowBypass = true): bool
    {
        if (!is_null($context) && !is_array($context) && !is_object($context)) {
            throw new TypeError('The context parameter must be an array or object.');
        }

        $permissions = $rawPermissionTree->getValue();

        if (array_key_exists('no_bypass', $permissions)) {
            $permissions['NO_BYPASS'] = $permissions['no_bypass'];
            unset($permissions['no_bypass']);
        }

        $allowBypass = $this->isBypassAllowed($permissions, $context, $allowBypass);

        unset($permissions['NO_BYPASS']);

        if ($allowBypass && $this->checkBypassAccess($context)) {
            return true;
        }

        return $this->deserializer->deserialize($permissions)->resolve($context);
    }

    /**
     * Checks if bypassing access is allowed.
     *
     * @param array             $permissions
     * @param array|object|null $context
     * @param bool              $allowBypass
     *
     * @return bool
     */
    protected function isBypassAllowed(array $permissions, $context, bool $allowBypass): bool
    {
        if (!$allowBypass) {
            return $allowBypass;
        }

        if (!array_key_exists('NO_BYPASS', $permissions)) {
            return $allowBypass;
        }

        $this->noBypassValidator->validateNoBypassValue($permissions['NO_BYPASS']);

        return !$this->deserializer->deserialize((array) $permissions['NO_BYPASS'])->resolve($context);
    }

    /**
     * Checks if access should be bypassed.
     *
     * @param array|object|null $context
     *
     * @return bool
     */
    protected function checkBypassAccess($context): bool
    {
        if (is_null($this->bypassAccessChecker)) {
            return false;
        }

        return $this->bypassAccessChecker->checkBypassAccess($context);
    }
}
