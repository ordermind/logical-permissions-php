<a href="https://travis-ci.org/Ordermind/logical-permissions-php" target="_blank"><img src="https://travis-ci.org/Ordermind/logical-permissions-php.svg?branch=master" /></a>
# logical-permissions-php

This is a generic library that provides support for array-based permissions with logic gates such as AND and OR. You can register any kind of permission types such as roles and flags. The idea with this library is to be an ultra-flexible foundation that can be used by any framework. Supported PHP version is 5.4 or higher. HHVM is also supported.

## Getting started

### Installation

`composer require ordermind/logical-permissions`

### Usage

The main api method is [`LogicalPermissions::checkAccess()`](#checkaccess), which checks the access for a **permission tree**. A permission tree is a bundle of permissions that apply to a specific action. Let's say for example that you want to restrict access for updating a user. You'd like only users with the role "admin" to be able to update any user, but users should also be able to update their own user data (or at least some of it). With the structure this package provides, these conditions could be expressed elegantly in a permission tree as such:

```php
[
  'OR' => [
    'role' => 'admin',
    'flag' => 'is_author',
  ],
]
```

In this example `role` and `flag` are the evaluated permission types. For this example to work you will need to register the permission types 'role' and 'flag' so that the class knows which callbacks are responsible for evaluating the respective permission types. You can do that with [`LogicalPermissions::addType()`](#addtype).

### Bypassing permissions
This packages also supports rules for bypassing permissions completely for superusers. In order to use this functionality you need to register a callback with [`LogicalPermissions::setBypassCallback()`](#setbypasscallback). The registered callback will run on every permission check and if it returns `TRUE`, access will automatically be granted. If you want to make exceptions you can do so by adding `'no_bypass' => TRUE` to the first level of a permission tree. You can even use permissions as conditions for `no_bypass`.

Examples: 

```php
//Disallow access bypassing
[
  'no_bypass' => TRUE,
  'role' => 'editor',
]
```

```php
//Disallow access bypassing only if the user is an admin
[
  'no_bypass' => [
    'role' => 'admin',
  ],
  'role' => 'editor',
]
```

## Logic gates

Currently supported logic gates are [AND](#and), [NAND](#nand), [OR](#or), [NOR](#nor), [XOR](#xor) and [NOT](#not). You can put logic gates anywhere in a permission tree and nest them to your heart's content. All logic gates support only an array as their value, except the NOT gate which has special rules. If an array of values does not have a logic gate as their key, an OR gate will be assumed.

### AND

A logic AND gate returns true if all of its children return true. Otherwise it returns false.

Examples:

```php
//Allow access only if the user is both an editor and a sales person
[
  'role' => [
    'AND' => ['editor', 'sales'],
  ],
]
```

```php
//Allow access if the user is both a sales person and the author of the document
[
  'AND' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### NAND

A logic NAND gate returns true if one or more of its children returns false. Otherwise it returns false.

Examples:

```php
//Allow access by anyone except if the user is both an editor and a sales person
[
  'role' => [
    'NAND' => ['editor', 'sales'],
  ],
]
```

```php
//Allow access by anyone, but not if the user is both a sales person and the author of the document.
[
  'NAND' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### OR

A logic OR gate returns true if one or more of its children returns true. Otherwise it returns false.

Examples:

```php
//Allow access if the user is either an editor or a sales person, or both.
[
  'role' => [
    'OR' => ['editor', 'sales'],
  ],
]
```

```php
//Allow access if the user is either a sales person or the author of the document, or both
[
  'OR' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### NOR

A logic NOR gate returns true if all of its children returns false. Otherwise it returns false.

Examples: 

```php
//Allow access if the user is neither an editor nor a sales person
[
  'role' => [
    'NOR' => ['editor', 'sales'],
  ],
]
```

```php
//Allow neither sales people nor the author of the document to access it
[
  'NOR' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```


### XOR

A logic XOR gate returns true if one or more of its children returns true and one or more of its children returns false. Otherwise it returns false. An XOR gate requires a minimum of two elements in its value array.

Examples:

```php
//Allow access if the user is either an editor or a sales person, but not both
[
  'role' => [
    'XOR' => ['editor', 'sales'],
  ],
]
```

```php
//Allow either sales people or the author of the document to access it, but not if the user is both a sales person and the author
[
  'XOR' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### NOT

A logic NOT gate returns true if its child returns false, and vice versa. The NOT gate is special in that it supports either a string or an array with a single element as its value.

Examples:

```php
//Allow access for anyone except editors
[
  'role' => [
    'NOT' => 'editor',
  ],
]
```

```php
//Allow access for anyone except the author of the document
[
  'NOT' => [
    'flag' => 'is_author',
  ],
]
```


## API Documentation

## Table of Contents

* [LogicalPermissions](#logicalpermissions)
    * [addType](#addtype)
    * [removeType](#removetype)
    * [typeExists](#typeexists)
    * [getTypeCallback](#gettypecallback)
    * [getTypes](#gettypes)
    * [setTypes](#settypes)
    * [getBypassCallback](#getbypasscallback)
    * [setBypassCallback](#setbypasscallback)
    * [checkAccess](#checkaccess)

## LogicalPermissions





* Full name: \Ordermind\LogicalPermissions\LogicalPermissions
* This class implements: \Ordermind\LogicalPermissions\LogicalPermissionsInterface



### addType

Add a permission type.

```php
LogicalPermissions::addType( string $name, callable $callback )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** | The name of the permission type. |
| `$callback` | **callable** | The callback that evaluates the permission type. |




---


### removeType

Remove a permission type.

```php
LogicalPermissions::removeType( string $name )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** | The name of the permission type. |




---


### typeExists

Checks whether a permission type is registered.

```php
LogicalPermissions::typeExists( string $name ): boolean
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** | The name of the permission type. |


**Return Value:**

TRUE if the type is found or FALSE if the type isn't found.



---


### getTypeCallback

Get the callback for a permission type.

```php
LogicalPermissions::getTypeCallback( string $name ): callable
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** | The name of the permission type. |


**Return Value:**

Callback for the permission type.



---


### getTypes

Get all defined permission types.

```php
LogicalPermissions::getTypes(  ): array
```





**Return Value:**

Permission types with the structure ['name' => callback, 'name2' => callback2, ...].



---


### setTypes

Overwrite all defined permission types.

```php
LogicalPermissions::setTypes( array $types )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$types` | **array** | Permission types with the structure ['name' => callback, 'name2' => callback2, ...]. |




---


### getBypassCallback

Get the registered callback for access bypass evaluation.

```php
LogicalPermissions::getBypassCallback(  ): callable
```





**Return Value:**

Bypass access callback.



---


### setBypassCallback

Set the callback for access bypass evaluation.

```php
LogicalPermissions::setBypassCallback( callable $callback )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** | The callback that evaluates access bypassing. |




---


### checkAccess

Check access for a permission tree.

```php
LogicalPermissions::checkAccess( array $permissions, array $context ): boolean
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$permissions` | **array** | The permission tree to be evaluated. |
| `$context` | **array** | A context array that could for example contain the evaluated user and document. |


**Return Value:**

Access.


