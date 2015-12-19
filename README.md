<a href="https://travis-ci.org/Ordermind/logical-permissions-php" target="_blank"><img src="https://travis-ci.org/Ordermind/logical-permissions-php.svg?branch=master" /></a>
# logical-permissions-php

This is a package that provides support for array-based permissions with logic gates such as AND and OR. You can register any kind of permission types such as roles and flags. The idea with this package is to be an ultra-flexible foundation that can be used by any framework. Supported PHP version is 5.4 or higher.

## Getting started

### Installation

`composer require ordermind/logical-permissions`

### Usage

The main api method is `LogicalPermissions::checkAccess()`, which checks the access for a **permission tree**. A permission tree is a bundle of permissions that apply to a specific action. Let's say for example that you want to restrict access for updating a user. You'd like only users with the role "admin" to be able to update any user, but users should also be able to update their own user data (or at least some of it). With the structure this package provides, these conditions could be expressed elegantly in a permission tree as such:

```php
[
  'OR': [
    'role' => 'admin',
    'flag' => 'is_author',
  ],
]
```

In order to do this you need to register the permission types 'role' and 'flag' so that the class knows which callbacks are responsible for evaluating the respective permission types. You can do that with `LogicalPermissions::addType()`.

### Bypassing permissions
This packages also supports rules for bypassing permissions completely for superusers. In order to use this functionality you need to register a callback with `LogicalPermissions::setBypassCallback()`. The registered callback will run on every permission check and if it returns `TRUE`, access will automatically be granted. If you want to make exceptions you can do so by adding `'no_bypass' => TRUE` to the first level of a permission tree.


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
LogicalPermissions::checkAccess( array $permissions, array $context )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$permissions` | **array** | The permission tree to be evaluated. |
| `$context` | **array** | A context array that could for example contain the evaluated user and document. |




---

