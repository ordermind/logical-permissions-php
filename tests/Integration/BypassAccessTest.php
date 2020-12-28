<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Integration;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\AccessChecker\BypassAccessCheckerDecorator;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\Factories\DefaultFullPermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\AlwaysAllowBypassChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\AlwaysDenyBypassChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use PHPUnit\Framework\TestCase;
use stdClass;

class BypassAccessTest extends TestCase
{
    private DefaultFullPermissionTreeDeserializerFactory $fullTreeDeserializerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fullTreeDeserializerFactory = new DefaultFullPermissionTreeDeserializerFactory();
    }

    /**
     * @dataProvider bypassAccessProvider
     */
    public function testBypassAccess(
        bool $expectedResult,
        AccessChecker $accessChecker,
        $permissions,
        $context = null,
        bool $allowBypass = true
    ) {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $this->assertSame(
            $expectedResult,
            $accessChecker->checkAccess($fullPermissionTree, $context, $allowBypass)
        );
    }

    public function bypassAccessProvider()
    {
        yield [
            true,
            new AccessChecker(new BypassAccessCheckerDecorator(new AlwaysAllowBypassChecker())),
            false,
        ];

        yield [
            false,
            new AccessChecker(new BypassAccessCheckerDecorator(new AlwaysDenyBypassChecker())),
            false,
        ];

        yield [
            false,
            new AccessChecker(new BypassAccessCheckerDecorator(new AlwaysAllowBypassChecker())),
            false,
            [],
            false,
        ];

        yield [
            true,
            new AccessChecker(new BypassAccessCheckerDecorator()),
            ['no_bypass' => true],
        ];

        yield [
            false,
            new AccessChecker(new BypassAccessCheckerDecorator(new AlwaysAllowBypassChecker())),
            ['no_bypass' => true, false],
            [],
        ];

        yield [
            true,
            new AccessChecker(new BypassAccessCheckerDecorator(new AlwaysAllowBypassChecker())),
            ['NO_BYPASS' => false],
        ];
    }

    public function testNoBypassArrayAllow()
    {
        $accessChecker = new AccessChecker(new BypassAccessCheckerDecorator(new AlwaysAllowBypassChecker()));
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new FlagPermissionChecker());

        $permissions = [
            'no_bypass' => [
                'flag' => 'never_bypass',
            ],
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $user = [
            'id'           => 1,
            'never_bypass' => false,
        ];
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user]));
    }

    public function testNoBypassArrayDeny()
    {
        $accessChecker = new AccessChecker(new BypassAccessCheckerDecorator(new AlwaysAllowBypassChecker()));
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new FlagPermissionChecker());

        $permissions = [
            'no_bypass' => [
                'flag' => 'never_bypass',
            ],
            false,
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $user = [
            'id'           => 1,
            'never_bypass' => true,
        ];
        $this->assertFalse($accessChecker->checkAccess($fullPermissionTree, ['user' => $user]));
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

        $accessChecker = new AccessChecker(new BypassAccessCheckerDecorator($bypassAccessChecker));
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize(false);

        $user = ['id' => 1];
        $accessChecker->checkAccess($fullPermissionTree, ['user' => $user]);
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

        $accessChecker = new AccessChecker(new BypassAccessCheckerDecorator($bypassAccessChecker));
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize(false);

        $context = new stdClass();
        $user = new stdClass();
        $user->id = 1;
        $context->user = $user;
        $accessChecker->checkAccess($fullPermissionTree, $context);
    }
}
