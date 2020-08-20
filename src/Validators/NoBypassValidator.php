<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Validators;

use TypeError;

/**
 * @internal
 */
class NoBypassValidator
{
    /**
     * Validates the NO_BYPASS value, throwing an exception if the validation fails.
     *
     * @param array|bool|string $noBypassValue
     *
     * @throws TypeError
     */
    public function validateNoBypassValue($noBypassValue): void
    {
        if (is_array($noBypassValue)) {
            return;
        }

        if (is_bool($noBypassValue)) {
            return;
        }

        if (is_string($noBypassValue) && in_array(strtoupper($noBypassValue), ['TRUE', 'FALSE'])) {
            return;
        }

        throw new TypeError(
            'The NO_BYPASS value must be a boolean, a boolean string or an array. Current value: '
                . print_r($noBypassValue, true)
        );
    }
}
