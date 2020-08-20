<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class RawPermissionTreeTest extends TestCase
{
    public function testValidation()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('The permissions parameter must be an array or in certain cases a string or '
            . 'boolean. Evaluated permissions: stdClass Object');
        new RawPermissionTree(new stdClass());
    }

    /**
     * @dataProvider provideTestGetValue
     */
    public function testGetValue(array $expected, $input)
    {
        $rawPermissionTree = new RawPermissionTree($input);

        $this->assertSame($expected, $rawPermissionTree->getValue());
    }

    public function provideTestGetValue()
    {
        return [
            [[true], true],
            [['true'], 'true'],
            [['role' => 'admin'], ['role' => 'admin']],
        ];
    }
}
