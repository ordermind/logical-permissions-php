<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Integration;

use Ordermind\LogicalPermissions\Debug\AccessChecker\DebugAccessCheckerResult;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeNodeValue;
use Ordermind\LogicalPermissions\Debug\PermissionTree\DebugPermissionTreeResult;
use Ordermind\LogicalPermissions\DefaultDebugAccessCheckerFactory;
use Ordermind\LogicalPermissions\DefaultFullPermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\Test\Fixtures\BypassChecker\AlwaysAllowBypassChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\ConditionPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use PHPUnit\Framework\TestCase;

class DebugCheckAccessTest extends TestCase
{
    private DefaultFullPermissionTreeDeserializerFactory $fullTreeDeserializerFactory;

    private DefaultDebugAccessCheckerFactory $debugAccessCheckerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fullTreeDeserializerFactory = new DefaultFullPermissionTreeDeserializerFactory();
        $this->debugAccessCheckerFactory = new DefaultDebugAccessCheckerFactory();
    }

    public function testEmptyArrayIsConvertedToBoolean()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize([]);
        $debugAccessChecker = $this->debugAccessCheckerFactory->create();

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                true,
                new DebugPermissionTreeNodeValue(true, true),
            ),
            null,
            [true],
            null
        );

        $this->assertEquals($expectedResult, $debugAccessChecker->checkAccess($fullPermissionTree));
    }

    /**
     * @dataProvider provideTestBooleanPermission
     */
    public function testBooleanPermissionIsNormalized(bool $access, $permissions)
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);
        $debugAccessChecker = $this->debugAccessCheckerFactory->create();

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                $access,
                new DebugPermissionTreeNodeValue($access, $access),
            ),
            null,
            [$access],
            null
        );

        $this->assertEquals($expectedResult, $debugAccessChecker->checkAccess($fullPermissionTree));
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
        $debugAccessChecker = $this->debugAccessCheckerFactory->create();

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
            $debugAccessChecker->checkAccess($fullPermissionTree, $context, false)
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
    public function testSingleItemNOT(bool $access, array $permissions, array $context)
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(new RolePermissionChecker());
        $debugAccessChecker = $this->debugAccessCheckerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

        $expectedNormalizedPermissions = [
            'NOT' => [
                ['role' => 'admin'],
            ],
        ];

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                $access,
                new DebugPermissionTreeNodeValue(
                    $access,
                    [
                        'NOT' => [
                            ['role' => 'admin'],
                        ],
                    ]
                ),
                new DebugPermissionTreeNodeValue(!$access, ['role' => 'admin']),
            ),
            null,
            $expectedNormalizedPermissions,
            $context
        );

        $this->assertEquals(
            $expectedResult,
            $debugAccessChecker->checkAccess($fullPermissionTree, $context, false)
        );
    }

    public function singleItemNOTProvider(): array
    {
        $permissionsWithStringChild = [
            'role' => [
                'NOT' => 'admin',
            ],
        ];

        $permissionsWithArrayChild = [
            'role' => [
                'NOT' => [
                    'admin',
                ],
            ],
        ];

        return [
            [false, $permissionsWithStringChild, ['user' => ['roles' => ['admin', 'editor']]]],
            [true, $permissionsWithStringChild, []],
            [true, $permissionsWithStringChild, ['user' => ['roles' => ['editor']]]],
            [false, $permissionsWithArrayChild, ['user' => ['roles' => ['admin', 'editor']]]],
            [true, $permissionsWithArrayChild, []],
            [true, $permissionsWithArrayChild, ['user' => ['roles' => ['editor']]]],
        ];
    }

    public function testMixedNestedPermissions()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create(
            new ConditionPermissionChecker(),
            new RolePermissionChecker(),
        );
        $debugAccessChecker = $this->debugAccessCheckerFactory->create();

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
                'user_is_author'   => true,
                'roles'            => [],
            ],
        ];

        $expectedNormalizedPermissions = [
            'OR' => [
                ['AND' => [
                    ['NOT' => [
                        ['AND' => [
                            ['role' => 'ROLE_ADMIN'],
                            ['role' => 'ROLE_ADMIN'],
                        ]],
                    ]],
                    true,
                    true,
                    ['NOT' => [
                        ['OR' => [
                            ['NOT' => [['condition' => 'user_has_account']]],
                            ['NOT' => [['condition' => 'user_is_author']]],
                        ]],
                    ]],
                ]],
                ['condition' => 'user_has_account'],
            ],
            'NO_BYPASS' => [
                'NOT' => [['condition' => 'user_has_account']],
            ],
        ];

        $expectedResult = new DebugAccessCheckerResult(
            false,
            new DebugPermissionTreeResult(
                true,
                new DebugPermissionTreeNodeValue(
                    true,
                    [
                        'OR' => [
                            ['AND' => [
                                ['NOT' => [
                                    ['AND' => [
                                        ['role' => 'ROLE_ADMIN'],
                                        ['role' => 'ROLE_ADMIN'],
                                    ]],
                                ]],
                                true,
                                true,
                                ['NOT' => [
                                    ['OR' => [
                                        ['NOT' => [['condition' => 'user_has_account']]],
                                        ['NOT' => [['condition' => 'user_is_author']]],
                                    ]],
                                ]],
                            ]],
                            ['condition' => 'user_has_account'],
                        ],
                    ]
                ),
                new DebugPermissionTreeNodeValue(
                    true,
                    [
                        'AND' => [
                            ['NOT' => [
                                ['AND' => [
                                    ['role' => 'ROLE_ADMIN'],
                                    ['role' => 'ROLE_ADMIN'],
                                ]],
                            ]],
                            true,
                            true,
                            ['NOT' => [
                                ['OR' => [
                                    ['NOT' => [['condition' => 'user_has_account']]],
                                    ['NOT' => [['condition' => 'user_is_author']]],
                                ]],
                            ]],
                        ],
                    ],
                ),
                new DebugPermissionTreeNodeValue(
                    true,
                    ['NOT' => [['AND' => [['role' => 'ROLE_ADMIN'], ['role' => 'ROLE_ADMIN']]]]]
                ),
                new DebugPermissionTreeNodeValue(
                    false,
                    ['AND' => [['role' => 'ROLE_ADMIN'], ['role' => 'ROLE_ADMIN']]]
                ),
                new DebugPermissionTreeNodeValue(false, ['role' => 'ROLE_ADMIN']),
                new DebugPermissionTreeNodeValue(false, ['role' => 'ROLE_ADMIN']),
                new DebugPermissionTreeNodeValue(true, true),
                new DebugPermissionTreeNodeValue(true, true),
                new DebugPermissionTreeNodeValue(
                    true,
                    [
                        'NOT' => [
                            ['OR' => [
                                ['NOT' => [['condition' => 'user_has_account']]],
                                ['NOT' => [['condition' => 'user_is_author']]],
                            ]],
                        ],
                    ],
                ),
                new DebugPermissionTreeNodeValue(
                    false,
                    [
                        'OR' => [
                            ['NOT' => [['condition' => 'user_has_account']]],
                            ['NOT' => [['condition' => 'user_is_author']]],
                        ],
                    ],
                ),
                new DebugPermissionTreeNodeValue(false, ['NOT' => [['condition' => 'user_has_account']]]),
                new DebugPermissionTreeNodeValue(true, ['condition' => 'user_has_account']),
                new DebugPermissionTreeNodeValue(false, ['NOT' => [['condition' => 'user_is_author']]]),
                new DebugPermissionTreeNodeValue(true, ['condition' => 'user_is_author']),
                new DebugPermissionTreeNodeValue(true, ['condition' => 'user_has_account']),
            ),
            new DebugPermissionTreeResult(
                false,
                new DebugPermissionTreeNodeValue(false, ['NOT' => [['condition' => 'user_has_account']]]),
                new DebugPermissionTreeNodeValue(true, ['condition' => 'user_has_account']),
            ),
            $expectedNormalizedPermissions,
            $context
        );

        $this->assertEquals(
            $expectedResult,
            $debugAccessChecker->checkAccess($fullPermissionTree, $context, false)
        );
    }

    public function testBypassAccess()
    {
        $fullTreeDeserializer = $this->fullTreeDeserializerFactory->create();
        $fullPermissionTree = $fullTreeDeserializer->deserialize(false);
        $debugAccessChecker = $this->debugAccessCheckerFactory->create(new AlwaysAllowBypassChecker());

        $expectedResult = new DebugAccessCheckerResult(
            true,
            new DebugPermissionTreeResult(
                false,
                new DebugPermissionTreeNodeValue(false, false),
            ),
            null,
            [false],
            null
        );

        $this->assertEquals($expectedResult, $debugAccessChecker->checkAccess($fullPermissionTree));
    }
}
