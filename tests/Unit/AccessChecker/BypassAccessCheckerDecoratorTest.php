<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class BypassAccessCheckerDecoratorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideTestIsBypassAllowed
     */
    public function testIsBypassAllowed(
        bool $expected,
        bool $allowBypass,
        bool $hasNoBypassTree,
        bool $evaluateResult
    ) {
        $context = [];

        $mockNoBypassTree = $this->prophesize(PermissionTree::class);
        $mockNoBypassTree->evaluate($context)->willReturn($evaluateResult);
        $noBypassTree = $mockNoBypassTree->reveal();

        $mockFullPermissionTree = $this->prophesize(FullPermissionTree::class);
        $mockFullPermissionTree->getNoBypassTree()->willReturn($noBypassTree);
        $mockFullPermissionTree->hasNoBypassTree()->willReturn($hasNoBypassTree);
        $fullPermissionTree = $mockFullPermissionTree->reveal();

        $decorator = new BypassAccessCheckerDecorator();
        $this->assertSame($expected, $decorator->isBypassAllowed($fullPermissionTree, $context, $allowBypass));
    }

    public function provideTestIsBypassAllowed(): array
    {
        return [
            [false, false, false, false],
            [false, false, false, true],
            [false, false, true, false],
            [false, false, true, true],
            [true, true, false, false],
            [true, true, false, true],
            [true, true, true, false],
            [false, true, true, true],
        ];
    }

    /**
     * @dataProvider provideTestCheckBypassAccess
     */
    public function testCheckBypassAccess(bool $expected, bool $hasBypassAccessChecker, bool $checkBypassAccessResult)
    {
        $context = [];

        $bypassAccessChecker = null;
        if ($hasBypassAccessChecker) {
            $mockBypassAccessChecker = $this->prophesize(BypassAccessCheckerInterface::class);
            $mockBypassAccessChecker->checkBypassAccess($context)->willReturn($checkBypassAccessResult);
            $bypassAccessChecker = $mockBypassAccessChecker->reveal();
        }

        $decorator = new BypassAccessCheckerDecorator($bypassAccessChecker);

        $this->assertSame($expected, $decorator->checkBypassAccess($context));
    }

    public function provideTestCheckBypassAccess(): array
    {
        return [
            [false, false, false],
            [false, false, true],
            [false, true, false],
            [true, true, true],
        ];
    }
}
