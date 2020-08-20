<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Integration;

use Ordermind\LogicalPermissions\LogicalPermissionsFacade;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\MiscPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use PHPUnit\Framework\TestCase;

class CheckAccessTest extends TestCase
{
    public function testEmptyArrayAllow()
    {
        $lpFacade = new LogicalPermissionsFacade();
        $this->assertTrue($lpFacade->checkAccess(new RawPermissionTree([])));
    }

    public function testSingleItemAllow()
    {
        $locator = new PermissionCheckerLocator([new FlagPermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator);

        $permissions = [
            'flag' => 'testflag',
        ];
        $user = [
            'id'       => 1,
            'testflag' => true,
        ];

        $this->assertTrue($lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user], false));
    }

    public function testSingleItemDeny()
    {
        $locator = new PermissionCheckerLocator([new FlagPermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator);

        $permissions = [
            'flag' => 'testflag',
        ];
        $user = [
            'id' => 1,
        ];
        $this->assertFalse($lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user], false));
    }

    /**
     * @dataProvider singleItemNOTProvider
     */
    public function testSingleItemNOT(bool $expectedResult, array $user)
    {
        $locator = new PermissionCheckerLocator([new RolePermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator);

        $permissions = [
            'role' => [
                'NOT' => 'admin',
            ],
        ];

        $this->assertSame(
            $expectedResult,
            $lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user], false)
        );

        $permissions = [
            'role' => [
                'NOT' => [
                    'admin',
                ],
            ],
        ];

        $this->assertSame(
            $expectedResult,
            $lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user], false)
        );
    }

    public function singleItemNOTProvider()
    {
        return [
            [false, ['roles' => ['admin', 'editor']]],
            [true, []],
            [true, ['roles' => ['editor']]],
        ];
    }

    /**
     * @dataProvider multipleTypesImplicitORProvider
     */
    public function testMultipleTypesImplicitOR(bool $expectedResult, array $user)
    {
        $locator = new PermissionCheckerLocator([
            new FlagPermissionChecker(),
            new RolePermissionChecker(),
            new MiscPermissionChecker(),
        ]);

        $lpFacade = new LogicalPermissionsFacade($locator);

        $permissions = [
            'flag' => 'testflag',
            'role' => 'admin',
            'misc' => 'test',
        ];

        $this->assertSame(
            $expectedResult,
            $lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user], false)
        );
    }

    public function multipleTypesImplicitORProvider()
    {
        return [                                                                // OR truth table
            [false, []],                                                        // 0 0 0
            [true, ['test' => true]],                                           // 0 0 1
            [true, ['roles' => ['admin']]],                                     // 0 1 0
            [true, ['test' => true, 'roles' => ['admin']]],                     // 0 1 1
            [true, ['testflag' => true]],                                       // 1 0 0
            [true, ['testflag' => true, 'test' => true]],                       // 1 0 1
            [true, ['testflag' => true, 'roles' => ['admin']]],                 // 1 1 0
            [true, ['testflag' => true, 'roles' => ['admin'], 'test' => true]], // 1 1 1
        ];
    }

    /**
     * @dataProvider multipleItemsProvider
     */
    public function testMultipleItems(bool $expectedResult, array $permissions, array $user)
    {
        $locator = new PermissionCheckerLocator([new RolePermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator);

        $this->assertSame(
            $expectedResult,
            $lpFacade->checkAccess(new RawPermissionTree($permissions), ['user' => $user], false)
        );
    }

    public function multipleItemsProvider()
    {
        $userTable = [
            [],                                                     // 0 0 0
            ['roles' => ['writer']],                                // 0 0 1
            ['roles' => ['editor']],                                // 0 1 0
            ['roles' => ['editor', 'writer']],                      // 0 1 1
            ['roles' => ['admin']],                                 // 1 0 0
            ['roles' => ['admin', 'writer']],                       // 1 0 1
            ['roles' => ['admin', 'editor']],                       // 1 1 0
            ['roles' => ['admin', 'editor', 'writer']],             // 1 1 1
        ];

        $truthTables = [
            //         0 0 0  0 0 1  0 1 0  0 1 1  1 0 0  1 0 1  1 1 0  1 1 1
            'AND'  => [false, false, false, false, false, false, false, true],
            'NAND' => [true,  true,  true,  true,  true,  true,  true,  false],
            'OR'   => [false, true,  true,  true,  true,  true,  true,  true],
            'NOR'  => [true,  false, false, false, false, false, false, false],
            'XOR'  => [false, true,  true,  true,  true,  true,  true,  false],
        ];

        foreach ($truthTables as $gateName => $truthTable) {
            foreach ($truthTable as $index => $expectedResult) {
                yield [
                    $expectedResult,
                    [
                        'role' => [
                            $gateName => [
                                'admin',
                                'editor',
                                'writer',
                            ],
                        ],
                    ],
                    $userTable[$index],
                ];
            }
        }

        // -- Implicit OR -- //
        foreach ($truthTables['OR'] as $index => $expectedResult) {
            yield [
                $expectedResult,
                [
                    'role' => [
                        [
                            'admin',
                            'editor',
                            'writer',
                        ],
                    ],
                ],
                $userTable[$index],
            ];
        }
    }

    /**
     * @dataProvider booleanPermissionProvider
     */
    public function testBooleanPermissions(bool $expectedResult, $permissions)
    {
        $lpFacade = new LogicalPermissionsFacade();

        $this->assertSame($expectedResult, $lpFacade->checkAccess(new RawPermissionTree($permissions)));
    }

    public function booleanPermissionProvider()
    {
        return [
            [true, true],
            [true, [true]],
            [true, 'TRUE'],
            [true, ['TRUE']],
            [true, 'true'],
            [true, ['true']],
            [false, false],
            [false, [false]],
            [false, 'FALSE'],
            [false, ['FALSE']],
            [false, 'false'],
            [false, ['false']],
        ];
    }

    /**
     * @dataProvider mixedBooleansProvider
     */
    public function testMixedBooleans(bool $expectedResult, $permissions)
    {
        $lpFacade = new LogicalPermissionsFacade();

        $this->assertSame($expectedResult, $lpFacade->checkAccess(new RawPermissionTree($permissions)));
    }

    public function mixedBooleansProvider()
    {
        return [
            [true, [false, true]],
            [true, ['OR' => [false, true]]],
            [false, ['AND' => [true, false]]],
        ];
    }

    public function testNestedLogic()
    {
        $locator = new PermissionCheckerLocator([new RolePermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator);

        $rawPermissionTree = new RawPermissionTree([
            'role' => [
                'OR' => [
                    'NOT' => [
                        'AND' => [
                            'admin',
                            'editor',
                        ],
                    ],
                ],
            ],
            false,
            'FALSE',
        ]);
        $user = [
            'id'    => 1,
            'roles' => ['admin', 'editor'],
        ];

        $this->assertFalse($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        unset($user['roles']);
        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor'];
        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
    }

    public function testLogicGateFirst()
    {
        $locator = new PermissionCheckerLocator([new RolePermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator);

        $rawPermissionTree = new RawPermissionTree([
            'AND' => [
                'role' => [
                    'OR' => [
                        'NOT' => [
                            'AND' => [
                                'admin',
                                'editor',
                            ],
                        ],
                    ],
                ],
                true,
                'TRUE',
            ],
        ]);
        $user = [
            'id'    => 1,
            'roles' => ['admin', 'editor'],
        ];

        $this->assertFalse($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        unset($user['roles']);
        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor'];
        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
    }

    public function testImplicitORMixedNumericStringKeys()
    {
        $locator = new PermissionCheckerLocator([new RolePermissionChecker()]);
        $lpFacade = new LogicalPermissionsFacade($locator);

        $rawPermissionTree = new RawPermissionTree([
            'role' => [
                'admin',
                'AND' => [
                    'editor',
                    'writer',
                    'OR' => [
                        'role1',
                        'role2',
                    ],
                ],
            ],
        ]);
        $user = [
            'id'    => 1,
            'roles' => ['admin'],
        ];

        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        unset($user['roles']);
        $this->assertFalse($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor'];
        $this->assertFalse($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor', 'writer'];
        $this->assertFalse($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor', 'writer', 'role1'];
        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor', 'writer', 'role2'];
        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
        $user['roles'] = ['admin', 'writer'];
        $this->assertTrue($lpFacade->checkAccess($rawPermissionTree, ['user' => $user], false));
    }
}
