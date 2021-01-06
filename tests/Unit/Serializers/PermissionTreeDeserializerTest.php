<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Serializers;

use Generator;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\Factories\LogicGateNodeFactory;
use Ordermind\LogicalPermissions\Locators\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTree;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\BooleanPermission;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\LogicGateNode;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\PermissionTreeNodeInterface;
use Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode\StringPermission;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Test\Fixtures\Factories\PermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicGates\AndGate;
use Ordermind\LogicGates\LogicGateFactory;
use Ordermind\LogicGates\NotGate;
use Ordermind\LogicGates\OrGate;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;
use UnexpectedValueException;

class PermissionTreeDeserializerTest extends TestCase
{
    /**
     * @dataProvider illegalPermissionsProvider
     */
    public function testIllegalPermissions(
        string $expectedClass,
        string $expectedMessage,
        $permissions
    ) {
        $locator = new PermissionCheckerLocator(new RolePermissionChecker(), new FlagPermissionChecker());
        $factory = new LogicGateNodeFactory(new LogicGateFactory());
        $deserializer = new PermissionTreeDeserializer($locator, $factory);

        $this->expectException($expectedClass);
        $this->expectExceptionMessage($expectedMessage);
        $deserializer->deserialize($permissions);
    }

    public function illegalPermissionsProvider()
    {
        yield [
            TypeError::class,
            'The permissions parameter must be an array or in certain cases a string or boolean. '
                . 'Evaluated permissions: stdClass Object',
            new stdClass(),
        ];

        yield [
            TypeError::class,
            'A permission must either be a boolean, a string or an array. Evaluated permissions: 50',
            ['flag' => 50],
        ];

        foreach (
            [
                [['role' => [true]]],
                [['role' => [false]]],
                [['role' => ['TRUE']]],
                [['role' => ['true']]],
                [['role' => ['FALSE']]],
                [['role' => ['false']]],
            ] as $permissions
        ) {
            yield [
                UnexpectedValueException::class,
                'You cannot put a boolean permission as a descendant to a permission type',
                $permissions,
            ];
        }

        yield [
            UnexpectedValueException::class,
            'You cannot use an empty string in a permission tree.',
            [''],
        ];

        yield [
            UnexpectedValueException::class,
            'A string value cannot be used in a permission tree without having a permission type as an ancestor. '
                . 'Evaluated permissions: "illegal_value"',
            ['illegal_value'],
        ];

        yield [
            UnexpectedValueException::class,
            'The NO_BYPASS key must be placed highest in the permission hierarchy',
            ['OR' => ['no_bypass' => true]],
        ];

        foreach (
            [
                [['TRUE' => false]],
                [['TRUE' => []]],
                [['FALSE' => false]],
                [['FALSE' => []]],
                [['true' => false]],
                [['true' => []]],
                [['false' => false]],
                [['false' => []]],
            ] as $permissions
        ) {
            yield [
                UnexpectedValueException::class,
                'A boolean permission cannot have children',
                $permissions,
            ];
        }

        foreach (
            [
                ['flag' => ['flag' => 'testflag']],
                ['flag' => ['OR' => ['flag' => 'testflag']]],
            ] as $permissions
        ) {
            yield [
                UnexpectedValueException::class,
                'You cannot put a permission type as a descendant to another permission type',
                $permissions,
            ];
        }
    }

    public function testDeserializeParamPermissionsUnregisteredType()
    {
        $factory = new LogicGateNodeFactory(new LogicGateFactory());
        $deserializer = new PermissionTreeDeserializer(new PermissionCheckerLocator(), $factory);

        $permissions = [
            'flag' => 'testflag',
        ];

        $this->expectException(PermissionTypeNotRegisteredException::class);
        $this->expectExceptionMessage('The permission type "flag" could not be found');
        $deserializer->deserialize($permissions);
    }

    /**
     * @dataProvider provideDeserialize
     */
    public function testDeserialize(PermissionTreeNodeInterface $expectedRootNode, $permissions)
    {
        $locator = new PermissionCheckerLocator(new RolePermissionChecker());
        $deserializer = PermissionTreeDeserializerFactory::create($locator);

        $this->assertEquals(new PermissionTree($expectedRootNode), $deserializer->deserialize($permissions));
    }

    public function provideDeserialize(): Generator
    {
        yield [
            new StringPermission(new RolePermissionChecker(), 'admin'),
            ['role' => 'admin'],
        ];

        yield [
            new BooleanPermission(true),
            true,
        ];

        yield [
            new LogicGateNode(new OrGate(
                new StringPermission(new RolePermissionChecker(), 'admin'),
                new StringPermission(new RolePermissionChecker(), 'editor'),
                new StringPermission(new RolePermissionChecker(), 'writer'),
            )),
            [
                ['role' => 'admin'],
                ['role' => 'editor'],
                ['role' => 'writer'],
            ],
        ];

        yield [
            new LogicGateNode(new AndGate(
                new StringPermission(new RolePermissionChecker(), 'admin'),
                new StringPermission(new RolePermissionChecker(), 'editor'),
            )),
            [
                'AND' => [
                    ['role' => 'admin'],
                    ['role' => 'editor'],
                ],
            ],
        ];

        // The AND gate has only one child, which means that it is always the same as the result of the child itself.
        // Therefore the AND gate is optimized away. The resulting array has two children, and is treated as an implicit
        // OR gate, which is made explicit by the deserializer.
        yield [
            new LogicGateNode(new OrGate(
                new StringPermission(new RolePermissionChecker(), 'admin'),
                new StringPermission(new RolePermissionChecker(), 'editor'),
            )),
            [
                'AND' => [
                    [
                        ['role' => 'admin'],
                        ['role' => 'editor'],
                    ],
                ],
            ],
        ];

        yield [
            new LogicGateNode(new AndGate(
                new LogicGateNode(new OrGate(
                    new StringPermission(new RolePermissionChecker(), 'admin'),
                    new StringPermission(new RolePermissionChecker(), 'editor'),
                )),
                new BooleanPermission(true)
            )),
            [
                'AND' => [
                    'role' => [
                        'admin',
                        'editor',
                    ],
                    true,
                ],
            ],
        ];

        yield [
            new LogicGateNode(new OrGate(
                new LogicGateNode(new NotGate(
                    new LogicGateNode(new AndGate(
                        new StringPermission(new RolePermissionChecker(), 'admin'),
                        new StringPermission(new RolePermissionChecker(), 'editor'),
                    ))
                )),
                new BooleanPermission(false),
                new BooleanPermission(false)
            )),
            [
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
            ],
        ];

        yield [
            new LogicGateNode(new AndGate(
                new LogicGateNode(new NotGate(
                    new LogicGateNode(new AndGate(
                        new StringPermission(new RolePermissionChecker(), 'admin'),
                        new StringPermission(new RolePermissionChecker(), 'editor'),
                    ))
                )),
                new BooleanPermission(true),
                new BooleanPermission(true)
            )),
            [
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
            ],
        ];

        yield [
            new LogicGateNode(new OrGate(
                new StringPermission(new RolePermissionChecker(), 'admin'),
                new LogicGateNode(new AndGate(
                    new StringPermission(new RolePermissionChecker(), 'editor'),
                    new StringPermission(new RolePermissionChecker(), 'writer'),
                    new LogicGateNode(new OrGate(
                        new StringPermission(new RolePermissionChecker(), 'role1'),
                        new StringPermission(new RolePermissionChecker(), 'role2'),
                    ))
                ))
            )),
            [
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
            ],
        ];
    }
}
