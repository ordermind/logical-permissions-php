<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Debug\AccessChecker;

use Ordermind\LogicalPermissions\Debug\AccessChecker\DebugAccessCheckerResult;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeResult;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DebugAccessCheckerResultTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideTestGetAccess
     */
    public function testGetAccess(bool $expected, bool $hasBypassedAccess, bool $mainTreeResultValue)
    {
        $mockMainTreeResult = $this->prophesize(DebugPermissionTreeResult::class);
        $mockMainTreeResult->getValue()->willReturn($mainTreeResultValue);
        $mainTreeResult = $mockMainTreeResult->reveal();

        $debugAccessCheckerResult = new DebugAccessCheckerResult(
            $hasBypassedAccess,
            $mainTreeResult,
            null,
            [],
            null
        );

        $this->assertSame($expected, $debugAccessCheckerResult->getAccess());
    }

    public function provideTestGetAccess(): array
    {
        return [
            [false, false, false],
            [true, false, true],
            [true, true, false],
            [true, true, true],
        ];
    }
}
