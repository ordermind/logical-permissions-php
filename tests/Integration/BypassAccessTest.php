<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Integration;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\LogicalPermissionsFacade;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\AlwaysAllowBypassChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\AlwaysDenyBypassChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use PHPUnit\Framework\TestCase;
use stdClass;

class BypassAccessTest extends TestCase
{
    /**
     * @dataProvider bypassAccessProvider
     */
    public function testBypassAccess(
        bool $expectedResult,
        LogicalPermissionsFacade $lpFacade,
        $permissions,
        $context = null,
        bool $allowBypass = true
    ) {
        $this->assertSame(
            $expectedResult,
            $lpFacade->checkAccess(new RawPermissionTree($permissions), $context, $allowBypass)
        );
    }

    public function bypassAccessProvider()
    {
        yield [
            true,
            new LogicalPermissionsFacade(null, new AlwaysAllowBypassChecker()),
            false,
        ];

        yield [
            false,
            new LogicalPermissionsFacade(null, new AlwaysDenyBypassChecker()),
            false,
        ];

        yield [
            false,
            new LogicalPermissionsFacade(null, new AlwaysAllowBypassChecker()),
            false,
            [],
            false,
        ];

        yield [
            true,
            new LogicalPermissionsFacade(),
            ['no_bypass' => true],
        ];

        yield [
            false,
            new LogicalPermissionsFacade(null, new AlwaysAllowBypassChecker()),
            ['no_bypass' => true, false],
            [],
        ];

        yield [
            true,
            new LogicalPermissionsFacade(null, new AlwaysAllowBypassChecker()),
            ['NO_BYPASS' => false],
        ];
    }

    public function testNoBypassArrayAllow()
    {
        $locator = new PermissionCheckerLocator([new FlagPermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator, new AlwaysAllowBypassChecker());

        $permissions = [
            'no_bypass' => [
                'flag' => 'never_bypass',
            ],
        ];
        $user = [
            'id'           => 1,
            'never_bypass' => false,
        ];
        $this->assertTrue($lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user]));
    }

    public function testNoBypassArrayDeny()
    {
        $locator = new PermissionCheckerLocator([new FlagPermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator, new AlwaysAllowBypassChecker());

        $permissions = [
            'no_bypass' => [
                'flag' => 'never_bypass',
            ],
            false,
        ];
        $user = [
            'id'           => 1,
            'never_bypass' => true,
        ];
        $this->assertFalse($lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user]));
    }

    public function testCheckBypassAccessContextArray()
    {
        $bypassAccessChecker = new class () extends BypassAccessTest implements BypassAccessCheckerInterface {
            public function checkBypassAccess($context): bool
            {
                $this->assertTrue(isset($context['user']['id']));
                $this->assertSame($context['user']['id'], 1);

                return true;
            }
        };

        $lpFacade = new LogicalPermissionsFacade(null, $bypassAccessChecker);

        $user = ['id' => 1];
        $lpFacade->checkAccess(new RawPermissionTree(false), ['user' => $user]);
    }

    public function testCheckBypassAccessContextObject()
    {
        $bypassAccessChecker = new class () extends BypassAccessTest implements BypassAccessCheckerInterface {
            public function checkBypassAccess($context): bool
            {
                $this->assertTrue(isset($context->user->id));
                $this->assertSame($context->user->id, 1);

                return true;
            }
        };

        $lpFacade = new LogicalPermissionsFacade(null, $bypassAccessChecker);

        $context = new stdClass();
        $user = new stdClass();
        $user->id = 1;
        $context->user = $user;
        $lpFacade->checkAccess(new RawPermissionTree(false), $context);
    }
}
