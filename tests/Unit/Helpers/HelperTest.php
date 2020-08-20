<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Helpers;

use Ordermind\LogicalPermissions\Helpers\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * @dataProvider flattenNumericArrayProvider
     */
    public function testFlattenNumericArray(array $expectedResult, array $inputValues)
    {
        $this->assertSame($expectedResult, Helper::flattenNumericArray($inputValues));
    }

    public function flattenNumericArrayProvider()
    {
        yield [
            [true],
            [true],
        ];

        yield [
            [
                true,
                true,
            ],
            [
                [
                    true,
                    true,
                ],
            ],
        ];

        yield [
            [
                true,
                true,
                true,
            ],
            [
                [true],
                [true],
                [true],
            ],
        ];

        yield [
            [
                true,
                true,
                true,
            ],
            [
                true,
                [true],
                [[[[[true]]]]],
            ],
        ];

        yield [
            [
                true,
                true,
                true,
                true,
                true,
            ],
            [
                [true],
                [[[[[true]]]]],
                [[true, true, [true]]],
            ],
        ];
    }
}
