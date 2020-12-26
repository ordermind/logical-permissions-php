<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\AccessChecker;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use TypeError;

class AccessCheckerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider checkAccessContextTypeProvider
     */
    public function testCheckAccessContextType(bool $expectException, $value)
    {
        $accessChecker = new AccessChecker();

        if ($expectException) {
            $this->expectException(TypeError::class);
            $this->expectExceptionMessage('The context parameter must be an array or object');
        }

        $mockMainTree = $this->prophesize(PermissionTree::class);
        $mockMainTree->evaluate(Argument::any())->willReturn(true);
        $mainTree = $mockMainTree->reveal();

        $mockFullPermissionTree = $this->prophesize(FullPermissionTree::class);
        $mockFullPermissionTree->getMainTree()->willReturn($mainTree);
        $mockFullPermissionTree->hasNoBypassTree()->willReturn(false);
        $fullPermissionTree = $mockFullPermissionTree->reveal();

        $accessChecker->checkAccess($fullPermissionTree, $value);

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
}
