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


## API

