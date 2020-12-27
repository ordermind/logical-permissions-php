<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Integration;

use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\AccessChecker\DebugAccessCheckerResult;
use Ordermind\LogicalPermissions\Factories\DefaultFullPermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\PermissionTree\DebugPermissionTreeResult;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\DebugPermissionTreeNodeValue;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\ConditionPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use PHPUnit\Framework\TestCase;

class CheckAccessDebugTest extends TestCase
{
    private DefaultFullPermissionTreeDeserializerFactory $fullTreeDeserializerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fullTreeDeserializerFactory = new DefaultFullPermissionTreeDeserializerFactory();
    }

    public function testEmptyArray()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize([]);
        $accessChecker = new AccessChecker();

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                true,
                new DebugPermissionTreeNodeValue(true, []),
            ),
            null,
            [],
            null
        );

        $this->assertEquals($expectedResult, $accessChecker->checkAccessWithDebug($fullPermissionTree));
    }

    /**
     * @dataProvider provideTestBooleanPermission
     */
    public function testBooleanPermission(bool $access, $permissions)
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);
        $accessChecker = new AccessChecker();

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                $access,
                new DebugPermissionTreeNodeValue($access, $permissions),
            ),
            null,
            $permissions,
            null
        );

        $this->assertEquals($expectedResult, $accessChecker->checkAccessWithDebug($fullPermissionTree));
    }

    public function provideTestBooleanPermission(): array
    {
        return [
            [false, false],
            [false, 'false'],
            [false, 'FALSE'],
            [true, true],
            [true, 'true'],
            [true, 'TRUE'],
        ];
    }

    /**
     * @dataProvider provideTestStringPermission
     */
    public function testStringPermission(bool $access, $permissions, array $context)
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new FlagPermissionChecker());
        $accessChecker = new AccessChecker();

        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                $access,
                new DebugPermissionTreeNodeValue($access, $permissions),
            ),
            null,
            $permissions,
            $context
        );

        $this->assertEquals(
            $expectedResult,
            $accessChecker->checkAccessWithDebug($fullPermissionTree, $context, false)
        );
    }

    public function provideTestStringPermission(): array
    {
        return [
            [false, ['flag' => 'testflag'], ['user' => ['id' => 1]]],
            [true, ['flag' => 'testflag'], ['user' => ['id' => 1, 'testflag' => true]]],
        ];
    }

    /**
     * @dataProvider singleItemNOTProvider
     */
    public function testSingleItemNOTString(bool $access, array $context)
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $accessChecker = new AccessChecker();

        $permissions = [
            'role' => [
                'NOT' => 'admin',
            ],
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                $access,
                new DebugPermissionTreeNodeValue(
                    $access,
                    [
                        'role' => [
                            'NOT' => 'admin',
                        ],
                    ]
                ),
                new DebugPermissionTreeNodeValue(
                    $access,
                    [
                        'NOT' => 'admin',
                    ]
                ),
                new DebugPermissionTreeNodeValue(!$access, 'admin'),
            ),
            null,
            $permissions,
            $context
        );

        $this->assertEquals(
            $expectedResult,
            $accessChecker->checkAccessWithDebug($fullPermissionTree, $context, false)
        );
    }

    /**
     * @dataProvider singleItemNOTProvider
     */
    public function testSingleItemNOTArray(bool $access, array $context)
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $accessChecker = new AccessChecker();

        $permissions = [
            'role' => [
                'NOT' => [
                    'admin',
                ],
            ],
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                $access,
                new DebugPermissionTreeNodeValue(
                    $access,
                    [
                        'role' => [
                            'NOT' => [
                                'admin',
                            ],
                        ],
                    ]
                ),
                new DebugPermissionTreeNodeValue(
                    $access,
                    [
                        'NOT' => [
                            'admin',
                        ],
                    ]
                ),
                new DebugPermissionTreeNodeValue(!$access, 'admin'),
            ),
            null,
            $permissions,
            $context
        );

        $this->assertEquals(
            $expectedResult,
            $accessChecker->checkAccessWithDebug($fullPermissionTree, $context, false)
        );
    }

    public function singleItemNOTProvider()
    {
        return [
            [false, ['user' => ['roles' => ['admin', 'editor']]]],
            [true, []],
            [true, ['user' => ['roles' => ['editor']]]],
        ];
    }

    public function testMixedNestedPermissions()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(
            new ConditionPermissionChecker(),
            new RolePermissionChecker(),
        );
        $accessChecker = new AccessChecker();

        $permissions = [
            'NO_BYPASS' => [
                'NOT' => [
                    'condition' => 'user_has_account',
                ],
            ],
            'AND' => [
                'role' => [
                    'OR' => [
                        'NOT' => [
                            'AND' => [
                                'ROLE_ADMIN',
                                'ROLE_ADMIN',
                            ],
                        ],
                    ],
                ],
                true,
                'TRUE',
                'condition' => [
                    'NOT' => [
                        'OR' => [
                            ['NOT' => 'user_has_account'],
                            ['NOT' => 'user_is_author'],
                        ],
                    ],
                ],
            ],
            'condition' => 'user_has_account',
        ];
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $context = [
            'user' => [
                'user_has_account' => true,
                'user_is_author'   => false,
                'roles'            => [],
            ],
        ];

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                true,
                new DebugPermissionTreeNodeValue(
                    true,
                    [
                        'AND' => [
                            'role'      => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]],
                            '0'         => true,
                            '1'         => 'TRUE',
                            'condition' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                        ],
                        'condition' => 'user_has_account',
                    ],
                ),
                new DebugPermissionTreeNodeValue(
                    true,
                    [
                        'AND' => [
                            'role'      => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]],
                            '0'         => true,
                            '1'         => 'TRUE',
                            'condition' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                        ],
                    ]
                ),
                new DebugPermissionTreeNodeValue(
                    true,
                    [
                        'condition' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                    ],
                ),
                new DebugPermissionTreeNodeValue(
                    true,
                    ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]]]
                ),
                new DebugPermissionTreeNodeValue(
                    true,
                    ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]]
                ),
                new DebugPermissionTreeNodeValue(
                    true,
                    ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]
                ),
                new DebugPermissionTreeNodeValue(
                    false,
                    ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]
                ),
                new DebugPermissionTreeNodeValue(false, 'ROLE_ADMIN'),
                new DebugPermissionTreeNodeValue(false, 'ROLE_ADMIN'),
                new DebugPermissionTreeNodeValue(true, true),
                new DebugPermissionTreeNodeValue(true, 'TRUE'),
                new DebugPermissionTreeNodeValue(
                    true,
                    ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                ),
                new DebugPermissionTreeNodeValue(
                    false,
                    ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]],
                ),
                new DebugPermissionTreeNodeValue(false, ['NOT' => 'user_has_account']),
                new DebugPermissionTreeNodeValue(true, 'user_has_account'),
                new DebugPermissionTreeNodeValue(false, ['NOT' => 'user_is_author']),
                new DebugPermissionTreeNodeValue(true, 'user_is_author'),
                new DebugPermissionTreeNodeValue(true, ['condition' => 'user_has_account']),
            ),
            new DebugPermissionTreeResult(
                false,
                new DebugPermissionTreeNodeValue(false, ['NOT' => 'user_has_account']),
                new DebugPermissionTreeNodeValue(true, 'user_has_account'),
            ),
            $permissions,
            $context
        );

        print_r($accessChecker->checkAccessWithDebug($fullPermissionTree, $context, false));

        // $this->assertEquals(
        //     $expectedResult,
        //     $accessChecker->checkAccessWithDebug($fullPermissionTree, $context, false)
        // );
    }
}
