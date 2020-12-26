<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Helpers;

/**
 * @internal
 */
class Helper
{
    /**
     * Flattens a multidimensional numeric array to a single level.
     *
     * @param array $array
     *
     * @return array
     */
    public static function flattenNumericArray(array $array): array
    {
        $return = [];
        array_walk_recursive($array, function ($value) use (&$return) {
            $return[] = $value;
        });

        return $return;
    }
}
