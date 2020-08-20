<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Helpers;

/**
 * @internal
 */
class Helper
{
    /**
     * Flattens a nested numeric array to a single level.
     *
     * @param array $array
     *
     * @return array
     */
    public static function flattenNumericArray(array $array): array
    {
        $newArray = [];

        foreach ($array as $value) {
            if (is_array($value)) {
                $newArray = array_merge($newArray, self::flattenNumericArray($value));
            } else {
                $newArray[] = $value;
            }
        }

        return $newArray;
    }
}
