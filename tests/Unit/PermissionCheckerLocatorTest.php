<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit;

use Ordermind\LogicalPermissions\Exceptions\InvalidPermissionTypeException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyRegisteredException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\AlwaysAllowPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\EmptyNamePermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\ErroneousNameTypePermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\IllegalNamePermissionChecker;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnexpectedValueException;

class PermissionCheckerLocatorTest extends TestCase
{
    // -----------PermissionCheckerLocator::fromIterable()-------------

    public function testFromIterable()
    {
        $checker1 = new AlwaysAllowPermissionChecker();
        $checker2 = new FlagPermissionChecker();

        $locator = new PermissionCheckerLocator($checker1, $checker2);
        $this->assertSame(['always_allow' => $checker1, 'flag' => $checker2], $locator->all());
        $this->assertEquals($locator, PermissionCheckerLocator::fromIterable([$checker1, $checker2]));
    }

    // -----------PermissionCheckerLocator::add()-------------

    public function testAddWrongNameType()
    {
        $locator = new PermissionCheckerLocator();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Return value of ' . ErroneousNameTypePermissionChecker::class . '::getName() must be of the type string'
        );
        $locator->add(new ErroneousNameTypePermissionChecker());
    }

    public function testAddEmptyName()
    {
        $locator = new PermissionCheckerLocator();

        $this->expectException(InvalidPermissionTypeException::class);
        $this->expectExceptionMessage(
            'The name of the permission checker ' . EmptyNamePermissionChecker::class . ' must not be empty'
        );
        $locator->add(new EmptyNamePermissionChecker());
    }

    public function testAddIllegalName()
    {
        $locator = new PermissionCheckerLocator();

        $this->expectException(InvalidPermissionTypeException::class);
        $this->expectExceptionMessage(
            'The permission checker ' . IllegalNamePermissionChecker::class . ' has the illegal name "and"'
        );
        $locator->add(new IllegalNamePermissionChecker());
    }

    public function testAddTypeAlreadyExists()
    {
        $locator = new PermissionCheckerLocator();
        $locator->add(new AlwaysAllowPermissionChecker());

        $this->expectException(PermissionTypeAlreadyRegisteredException::class);
        $this->expectExceptionMessage('The permission type "always_allow" is already registered');
        $locator->add(new AlwaysAllowPermissionChecker());
    }

    public function testAddTypeOverwriteExisting()
    {
        $locator = new PermissionCheckerLocator();
        $locator->add(new AlwaysAllowPermissionChecker());
        $locator->add(new AlwaysAllowPermissionChecker(), true);

        $this->addToAssertionCount(1);
    }

    public function testAdd()
    {
        $locator = new PermissionCheckerLocator();
        $locator->add(new AlwaysAllowPermissionChecker());
        $this->assertTrue($locator->has('always_allow'));
    }

    public function testAddUpdate()
    {
        $locator = new PermissionCheckerLocator();
        $locator->add(new AlwaysAllowPermissionChecker());
        $locator->add(new AlwaysAllowPermissionChecker(), true);

        $this->addToAssertionCount(1);
    }

    // -------------PermissionCheckerLocator::remove()--------------

    public function testRemoveEmptyName()
    {
        $locator = new PermissionCheckerLocator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The name must not be empty');
        $locator->remove('');
    }

    public function testRemoveUnregisteredType()
    {
        $locator = new PermissionCheckerLocator();
        $locator->remove('test');

        $this->addToAssertionCount(1);
    }

    public function testRemove()
    {
        $locator = new PermissionCheckerLocator(new AlwaysAllowPermissionChecker());
        $locator->remove('always_allow');
        $this->assertFalse($locator->has('always_allow'));
    }

    // ------------PermissionCheckerLocator::has()---------------

    public function testHasEmptyName()
    {
        $locator = new PermissionCheckerLocator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The name must not be empty');
        $locator->has('');
    }

    public function testHas()
    {
        $locator = new PermissionCheckerLocator();
        $this->assertFalse($locator->has('always_allow'));
        $locator->add(new AlwaysAllowPermissionChecker());
        $this->assertTrue($locator->has('always_allow'));
    }

    // ------------PermissionCheckerLocator::get()---------------

    public function testGetEmptyName()
    {
        $locator = new PermissionCheckerLocator();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The name must not be empty');
        $locator->get('');
    }

    public function testGetUnregisteredType()
    {
        $locator = new PermissionCheckerLocator();

        $this->expectException(PermissionTypeNotRegisteredException::class);
        $this->expectExceptionMessage('The permission type "unregistered" has not been registered');
        $locator->get('unregistered');
    }

    public function testGet()
    {
        $permissionChecker = new AlwaysAllowPermissionChecker();
        $locator = new PermissionCheckerLocator($permissionChecker);

        $this->assertSame($locator->get('always_allow'), $permissionChecker);
    }

    public function testAll()
    {
        $locator = new PermissionCheckerLocator();
        $this->assertSame([], $locator->all());

        $checker1 = new AlwaysAllowPermissionChecker();
        $checker2 = new FlagPermissionChecker();
        $locator->add($checker1);
        $locator->add($checker2);
        $this->assertSame(['always_allow' => $checker1, 'flag' => $checker2], $locator->all());
    }

    // ------------PermissionCheckerLocator::getValidPermissionTreeKeys()---------------

    public function testGetValidPermissionKeys()
    {
        $locator = new PermissionCheckerLocator();

        $this->assertSame(
            [
                'AND',
                'NAND',
                'OR',
                'NOR',
                'XOR',
                'NOT',
                'NO_BYPASS',
                'TRUE',
                'FALSE',
            ],
            $locator->getValidPermissionTreeKeys()
        );

        $locator->add(new AlwaysAllowPermissionChecker());
        $this->assertSame(
            [
                'AND',
                'NAND',
                'OR',
                'NOR',
                'XOR',
                'NOT',
                'NO_BYPASS',
                'TRUE',
                'FALSE',
                'always_allow',
            ],
            $locator->getValidPermissionTreeKeys()
        );
    }
}
