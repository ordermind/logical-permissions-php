<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Integration;

use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeSerializer;
use Ordermind\LogicalPermissions\Test\Fixtures\Factories\PermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use PHPUnit\Framework\TestCase;

class TreeSerializationTest extends TestCase
{
    /**
     * @dataProvider permissionTreeSerializationProvider
     */
    public function testPermissionTreeSerialization($expectedOutput, $permissions)
    {
        $locator = new PermissionCheckerLocator([new RolePermissionChecker()]);
        $deserializer = PermissionTreeDeserializerFactory::create($locator);
        $serializer = new PermissionTreeSerializer();

        // 1st pass
        $permissionTree = $deserializer->deserialize($permissions);
        $output = $serializer->serialize($permissionTree);
        $this->assertSame($expectedOutput, $output);

        // 2nd pass, to ensure that it is consistent if we feed back the serialized output.
        $permissionTree = $deserializer->deserialize($expectedOutput);
        $output = $serializer->serialize($permissionTree);
        $this->assertSame($expectedOutput, $output);
    }

    public function permissionTreeSerializationProvider()
    {
        yield [
            ['role' => 'admin'],
            ['role' => 'admin'],
        ];

        yield [
            [true],
            [true],
        ];

        yield [
            [
                'OR' => [
                    ['role' => 'admin'],
                    ['role' => 'editor'],
                    ['role' => 'writer'],
                ],
            ],
            [
                ['role' => 'admin'],
                ['role' => 'editor'],
                ['role' => 'writer'],
            ],
        ];

        yield [
            [
                'AND' => [
                    ['role' => 'admin'],
                    ['role' => 'editor'],
                ],
            ],
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
            [
                'OR' => [
                    ['role' => 'admin'],
                    ['role' => 'editor'],
                ],
            ],
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
            [
                'AND' => [
                    [
                        'OR' => [
                            ['role' => 'admin'],
                            ['role' => 'editor'],
                        ],
                    ],
                    true,
                ],
            ],
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
            [
                'OR' => [
                    ['NOT' => [
                        ['AND' => [
                            ['role' => 'admin'],
                            ['role' => 'editor'],
                        ]],
                    ]],
                    false,
                    false,
                ],
            ],
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
            [
                'AND' => [
                    ['NOT' => [
                        ['AND' => [
                            ['role' => 'admin'],
                            ['role' => 'editor'],
                        ]],
                    ]],
                    true,
                    true,
                ],
            ],
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
            [
                'OR' => [
                    ['role' => 'admin'],
                    ['AND'  => [
                        ['role' => 'editor'],
                        ['role' => 'writer'],
                        ['OR'    => [
                            ['role' => 'role1'],
                            ['role' => 'role2'],
                        ]],
                    ]],
                ],
            ],
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
