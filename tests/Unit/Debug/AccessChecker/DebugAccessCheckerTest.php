<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Debug\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\Debug\AccessChecker\DebugAccessChecker;
use Ordermind\LogicalPermissions\Debug\AccessChecker\DebugAccessCheckerResult;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeEvaluator;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeResult;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeSerializer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use TypeError;

class DebugAccessCheckerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider checkAccessContextTypeProvider
     */
    public function testCheckAccessContextType(bool $expectException, $context)
    {
        if ($expectException) {
            $this->expectException(TypeError::class);
            $this->expectExceptionMessage('The context parameter must be an array or object');
        }

        $mainTree = $this->prophesize(PermissionTree::class)->reveal();

        $mockFullPermissionTree = $this->prophesize(FullPermissionTree::class);
        $mockFullPermissionTree->getMainTree()->willReturn($mainTree);
        $mockFullPermissionTree->hasNoBypassTree()->willReturn(false);
        $fullPermissionTree = $mockFullPermissionTree->reveal();

        $bypassAccessCheckerDecorator = new BypassAccessCheckerDecorator();

        $mockDebugTreeEvaluator = $this->prophesize(DebugPermissionTreeEvaluator::class);
        $mockDebugTreeEvaluator->evaluate(Argument::cetera())->willReturn(new DebugPermissionTreeResult(true));
        $debugTreeEvaluator = $mockDebugTreeEvaluator->reveal();

        $mockFullPermissionTreeSerializer = $this->prophesize(FullPermissionTreeSerializer::class);
        $mockFullPermissionTreeSerializer->serialize($fullPermissionTree)->willReturn([]);
        $fullPermissionTreeSerializer = $mockFullPermissionTreeSerializer->reveal();

        $debugAccessChecker = new DebugAccessChecker(
            $bypassAccessCheckerDecorator,
            $debugTreeEvaluator,
            $fullPermissionTreeSerializer
        );

        $debugAccessChecker->checkAccess($fullPermissionTree, $context);

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
        bool $expectedHasBypassedAccess,
        bool $hasNoBypassTree,
        bool $isBypassAllowed,
        bool $bypassAccessResult
    ) {
        $context = [];

        $mainTree = $this->prophesize(PermissionTree::class)->reveal();
        $noBypassTree = $this->prophesize(PermissionTree::class)->reveal();

        $mockFullPermissionTree = $this->prophesize(FullPermissionTree::class);
        $mockFullPermissionTree->getMainTree()->willReturn($mainTree);
        $mockFullPermissionTree->hasNoBypassTree()->willReturn($hasNoBypassTree);
        $mockFullPermissionTree->getNoBypassTree()->willReturn($noBypassTree);
        $fullPermissionTree = $mockFullPermissionTree->reveal();

        $mockBypassAccessCheckerDecorator = $this->prophesize(BypassAccessCheckerDecorator::class);
        $mockBypassAccessCheckerDecorator
            ->isBypassAllowed($fullPermissionTree, $context, true)
            ->willReturn($isBypassAllowed);
        $mockBypassAccessCheckerDecorator->checkBypassAccess($context)->willReturn($bypassAccessResult);
        $bypassAccessCheckerDecorator = $mockBypassAccessCheckerDecorator->reveal();

        $mockDebugTreeEvaluator = $this->prophesize(DebugPermissionTreeEvaluator::class);
        $mockDebugTreeEvaluator->evaluate($mainTree, $context)->willReturn(new DebugPermissionTreeResult(true));
        $mockDebugTreeEvaluator->evaluate($noBypassTree, $context)->willReturn(new DebugPermissionTreeResult(false));
        $debugTreeEvaluator = $mockDebugTreeEvaluator->reveal();

        $mockFullPermissionTreeSerializer = $this->prophesize(FullPermissionTreeSerializer::class);
        $mockFullPermissionTreeSerializer->serialize($fullPermissionTree)->willReturn([true]);
        $fullPermissionTreeSerializer = $mockFullPermissionTreeSerializer->reveal();

        $expectedMainTreeResult = new DebugPermissionTreeResult(true);

        $expectedNoBypassTreeResult = null;
        if ($hasNoBypassTree) {
            $expectedNoBypassTreeResult = new DebugPermissionTreeResult(false);
        }

        $expected = new DebugAccessCheckerResult(
            $expectedHasBypassedAccess,
            $expectedMainTreeResult,
            $expectedNoBypassTreeResult,
            [true],
            $context
        );

        $debugAccessChecker = new DebugAccessChecker(
            $bypassAccessCheckerDecorator,
            $debugTreeEvaluator,
            $fullPermissionTreeSerializer
        );

        $this->assertEquals($expected, $debugAccessChecker->checkAccess($fullPermissionTree, $context));
    }

    public function provideTestCheckAccess(): array
    {
        return [
            [false, false, false, false],
            [false, false, false, true],
            [false, false, true, false],
            [true, false, true, true],
            [false, true, false, false],
            [false, true, false, true],
            [false, true, true, false],
            [true, true, true, true],
        ];
    }
}
