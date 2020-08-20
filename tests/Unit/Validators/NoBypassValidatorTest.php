<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Validators;

use Ordermind\LogicalPermissions\Validators\NoBypassValidator;
use PHPUnit\Framework\TestCase;
use TypeError;

class NoBypassValidatorTest extends TestCase
{
    /**
     * @var NoBypassValidator
     */
    private $validator;

    protected function setup(): void
    {
        parent::setUp();

        $this->validator = new NoBypassValidator();
    }

    /**
     * @dataProvider validateNoBypassValueProvider
     */
    public function testValidateNoBypassValue(bool $expectException, $value)
    {
        if ($expectException) {
            $this->expectException(TypeError::class);
            $this->expectExceptionMessage('The NO_BYPASS value must be a boolean, a boolean string or an array');
        }
        $this->validator->validateNoBypassValue($value);
        $this->addToAssertionCount(1);
    }

    public function validateNoBypassValueProvider()
    {
        return [
            [false, true],
            [false, []],
            [false, 'TRUE'],
            [false, 'true'],
            [false, 'FALSE'],
            [false, 'false'],
            [true, 'illegal_string'],
            [true, null],
        ];
    }
}
