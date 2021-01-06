<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Integration;

use Ordermind\LogicalPermissions\DefaultAccessCheckerFactory;
use Ordermind\LogicalPermissions\DefaultFullPermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\MiscPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use PHPUnit\Framework\TestCase;

class CheckAccessTest extends TestCase
{
    private DefaultFullPermissionTreeDeserializerFactory $fullTreeDeserializerFactory;
    private DefaultAccessCheckerFactory $accessCheckerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fullTreeDeserializerFactory = new DefaultFullPermissionTreeDeserializerFactory();
        $this->accessCheckerFactory = new DefaultAccessCheckerFactory();
    }

    public function testEmptyArrayAllow()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize([]);
        $accessChecker = $this->accessCheckerFactory->create();
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree));
    }

    public function testSingleItemAllow()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new FlagPermissionChecker());
        $accessChecker = $this->accessCheckerFactory->create();

        $permissions = [
            'flag' => 'testflag',
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $user = [
            'id'       => 1,
            'testflag' => true,
        ];

        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
    }

    public function testSingleItemDeny()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new FlagPermissionChecker());
        $accessChecker = $this->accessCheckerFactory->create();

        $permissions = [
            'flag' => 'testflag',
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $user = [
            'id' => 1,
        ];

        $this->assertFalse($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
    }

    /**
     * @dataProvider singleItemNOTProvider
     */
    public function testSingleItemNOT(bool $expectedResult, array $user)
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $accessChecker = $this->accessCheckerFactory->create();

        $permissions = [
            'role' => [
                'NOT' => 'admin',
            ],
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $this->assertSame(
            $expectedResult,
            $accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false)
        );

        $permissions = [
            'role' => [
                'NOT' => [
                    'admin',
                ],
            ],
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $this->assertSame(
            $expectedResult,
            $accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false)
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
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(
            new FlagPermissionChecker(),
            new RolePermissionChecker(),
            new MiscPermissionChecker()
        );
        $accessChecker = $this->accessCheckerFactory->create();

        $permissions = [
            'flag' => 'testflag',
            'role' => 'admin',
            'misc' => 'test',
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $this->assertSame(
            $expectedResult,
            $accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false)
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
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $accessChecker = $this->accessCheckerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $this->assertSame(
            $expectedResult,
            $accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false)
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
            'XOR'  => [false, true,  true,  false, true,  false, false, true],
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
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $accessChecker = $this->accessCheckerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $this->assertSame($expectedResult, $accessChecker->checkAccess($fullPermissionTree));
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
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $accessChecker = $this->accessCheckerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $this->assertSame($expectedResult, $accessChecker->checkAccess($fullPermissionTree));
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
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $accessChecker = $this->accessCheckerFactory->create();

        $permissions = [
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
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $user = [
            'id'    => 1,
            'roles' => ['admin', 'editor'],
        ];

        $this->assertFalse($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        unset($user['roles']);
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor'];
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
    }

    public function testLogicGateFirst()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $accessChecker = $this->accessCheckerFactory->create();

        $permissions = [
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
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $user = [
            'id'    => 1,
            'roles' => ['admin', 'editor'],
        ];

        $this->assertFalse($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        unset($user['roles']);
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor'];
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
    }

    public function testImplicitORMixedNumericStringKeys()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $accessChecker = $this->accessCheckerFactory->create();

        $permissions = [
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
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $user = [
            'id'    => 1,
            'roles' => ['admin'],
        ];

        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        unset($user['roles']);
        $this->assertFalse($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor'];
        $this->assertFalse($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor', 'writer'];
        $this->assertFalse($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor', 'writer', 'role1'];
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        $user['roles'] = ['editor', 'writer', 'role2'];
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
        $user['roles'] = ['admin', 'writer'];
        $this->assertTrue($accessChecker->checkAccess($fullPermissionTree, ['user' => $user], false));
    }
}
