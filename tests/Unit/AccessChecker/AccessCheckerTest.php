<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use TypeError;

class AccessCheckerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider checkAccessContextTypeProvider
     */
    public function testCheckAccessContextType(bool $expectException, $context)
    {
        $accessChecker = new AccessChecker(new BypassAccessCheckerDecorator());

        if ($expectException) {
            $this->expectException(TypeError::class);
            $this->expectExceptionMessage('The context parameter must be an array or object');
        }

        $mockMainTree = $this->prophesize(PermissionTree::class);
        $mockMainTree->evaluate($context)->willReturn(true);
        $mainTree = $mockMainTree->reveal();

        $mockFullPermissionTree = $this->prophesize(FullPermissionTree::class);
        $mockFullPermissionTree->getMainTree()->willReturn($mainTree);
        $mockFullPermissionTree->hasNoBypassTree()->willReturn(false);
        $fullPermissionTree = $mockFullPermissionTree->reveal();

        $accessChecker->checkAccess($fullPermissionTree, $context);

        $this->addToAssertionCount(1);
    }

    public function checkAccessContextTypeProvider()
    {
        return [
            [false, null],
            [false, []],
            [false, new stdClass()],
            [true, 'string'],
            [true, 0],
        ];
    }

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
