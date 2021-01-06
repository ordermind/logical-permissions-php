<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AccessCheckerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideTestCheckAccess
     */
    public function testCheckAccess(
        bool $expected,
        bool $evaluateResult,
        bool $isBypassAllowed,
        bool $bypassAccessResult
    ) {
        $context = [];

        $mockMainTree = $this->prophesize(PermissionTree::class);
        $mockMainTree->evaluate($context)->willReturn($evaluateResult);
        $mainTree = $mockMainTree->reveal();

        $mockFullPermissionTree = $this->prophesize(FullPermissionTree::class);
        $mockFullPermissionTree->getMainTree()->willReturn($mainTree);
        $mockFullPermissionTree->hasNoBypassTree()->willReturn(false);
        $fullPermissionTree = $mockFullPermissionTree->reveal();

        $mockBypassAccessCheckerDecorator = $this->prophesize(BypassAccessCheckerDecorator::class);
        $mockBypassAccessCheckerDecorator
            ->isBypassAllowed($fullPermissionTree, $context, true)
            ->willReturn($isBypassAllowed);
        $mockBypassAccessCheckerDecorator->checkBypassAccess($context)->willReturn($bypassAccessResult);
        $bypassAccessCheckerDecorator = $mockBypassAccessCheckerDecorator->reveal();

        $accessChecker = new AccessChecker($bypassAccessCheckerDecorator);

        $this->assertSame($expected, $accessChecker->checkAccess($fullPermissionTree, $context));
    }

    public function provideTestCheckAccess(): array
    {
        return [
            [false, false, false, false],
            [false, false, false, true],
            [false, false, true, false],
            [true, false, true, true],
            [true, true, false, false],
            [true, true, false, true],
            [true, true, true, false],
            [true, true, true, true],
        ];
    }
}
