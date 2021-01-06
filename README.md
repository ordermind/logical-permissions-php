<a href="https://travis-ci.org/ordermind/logical-permissions-php" target="_blank"><img src="https://travis-ci.org/ordermind/logical-permissions-php.svg?branch=3.x" /></a>
# logical-permissions

This is a generic library that provides support for array-based permissions with logic gates such as AND and OR. You can register permission checkers for any kind of permission types such as roles and conditions. The idea with this library is to be an ultra-flexible foundation that can be used by any framework.

## Getting started

### Installation

`composer require ordermind/logical-permissions`

### Usage

#### Permission trees
A central concept within this library is the **permission tree**. A permission tree is a hierarchical combination of permissions following a certain syntax that is evaluated in order to determine access for a specific action.

Let's say for example that you want to restrict access for updating a user. You'd like only users with the role "admin" to be able to update any user, but users should also be able to update their own user data (or at least some of it). With the format that this library provides, these conditions could be expressed elegantly in a permission tree as such:

```php
[
    'OR' => [
        'role' => 'admin',
        'condition' => 'is_author',
    ],
]
```

In this example `role` and `condition` are the evaluated permission types. For this example to work you will need to register the permission types 'role' and 'condition'. Read on to find out how you can do that.

#### Register permission checkers

Permission checkers are used to evaluate parts of the permission tree, and the first thing to do is to create one of these and register it. Let's say, for example, that we want to determine access using the current user's roles. First you create a class that implements ```Ordermind\LogicalPermissions\PermissionCheckerInterface``` like this:

```php
use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class MyPermissionChecker implements PermissionCheckerInterface 
{
    public function getName() : string 
    {
        return 'role';
    }

    public function checkPermission(string $role, $context) : bool 
    {
        if (!empty($context['user']['roles'])) {
            return in_array($role, $context['user']['roles']);
        }

        return false;
    }
}
```
Now we have implemented the two required methods - getName() and checkPermission() - and created a simple example for checking a role for a user. The name of the permission checker is the permission type, to be used later as a key in your permission tree. The checkPermission() method is where you, in this case, check whether the current user has a role or not.

Once you have created a permission checker you can register it like this:

```php
use Ordermind\LogicalPermissions\DefaultFullPermissionTreeDeserializerFactory;

$fullTreeDeserializerFactory = new DefaultFullPermissionTreeDeserializerFactory();
$fullTreeDeserializer = $fullTreeDeserializerFactory->create(new MyPermissionChecker());
```
#### Check access

Now everything is set and you can check the access for a user based on their roles:
```php
use Ordermind\LogicalPermissions\DefaultAccessCheckerFactory;

$permissions = [
    'role' => 'admin', // The key 'role' here is the value that you return in the getName() method of your permission checker
];
$fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

$user = ['roles' => ['admin', 'sales']];

$accessCheckerFactory = new DefaultAccessCheckerFactory();
$accessChecker = $accessCheckerFactory->create();
$access = $accessChecker->checkAccess($fullPermissionTree, ['user' => $user]); // true
```

#### Bypassing access checks
This library also supports rules for bypassing access checks completely for superusers. In order to use this functionality you first need to create a class that implements ```Ordermind\LogicalPermissions\BypassAccessCheckerInterface``` like this:

```php
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class MyBypassAccessChecker implements BypassAccessCheckerInterface 
{
    public function checkBypassAccess($context) : bool 
    {
        if($context['user']['id'] == 1) {
            return true;
        }

        return false;
    }
}
```

Then you can register it like this:
```php
use Ordermind\LogicalPermissions\DefaultAccessCheckerFactory;

$accessCheckerFactory = new DefaultAccessCheckerFactory();
$accessChecker = $accessCheckerFactory->create(new MyBypassAccessChecker());
```

From now on, every time you call ```$accessChecker->checkAccess()``` the user with the id 1 will be exempted so that no matter what the permissions are, they will always be granted access. If you want to make exceptions, you can do so by adding `'NO_BYPASS' => true` to the first level of a permission tree. You can even use permissions as conditions for `NO_BYPASS`.

Examples:

```php
//Disallow access bypassing
[
    'NO_BYPASS' => true,
    'role' => 'editor',
]
```

```php
//Disallow access bypassing only if the user is an admin
[
    'NO_BYPASS' => [
        'role' => 'admin',
    ],
    'role' => 'editor',
]
```

#### Debugging access checks

This library also provides a way to get information about every part of the permission tree during the access check, for easier debugging. In order to do that, you need to use the `DebugAccessChecker` instead of `AccessChecker` like so:

```php
use Ordermind\LogicalPermissions\DefaultDebugAccessCheckerFactory;

$permissions = [
    'role' => 'admin', // The key 'role' here is the value that you return in the getName() method of your permission checker
];
$fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

$user = ['roles' => ['admin', 'sales']];

$debugAccessCheckerFactory = new DefaultDebugAccessCheckerFactory();
$debugAccessChecker = $debugAccessCheckerFactory->create();
$result = $debugAccessChecker->checkAccess($fullPermissionTree, ['user' => $user]);
```

## Logic gates

Currently supported logic gates are [AND](#and), [NAND](#nand), [OR](#or), [NOR](#nor), [XOR](#xor) and [NOT](#not). You can put logic gates anywhere in a permission tree and nest them to your heart's content. All logic gates support only an array as their value, except the NOT gate which has special rules. If an array of values does not have a logic gate as its key, an OR gate will be assumed.

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
        'condition' => 'is_author',
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
        'condition' => 'is_author',
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
        'condition' => 'is_author',
    ],
]
```

### Implicit OR

As previously mentioned, any array of values that doesn't have a logic gate as its key is interpreted as belonging to an OR gate.

In other words, this permission tree:

```php
[
    'role' => ['editor', 'sales'],
]
```
is interpreted exactly the same way as this permission tree:
```php
[
    'role' => [
        'OR' => ['editor', 'sales'],
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
        'condition' => 'is_author',
    ],
]
```


### XOR

The output of the XOR gate is `true` if the number of `true` input values is odd, otherwise the output is `false`. An XOR gate requires a minimum of two elements in its value array. If the number of input values for the XOR gate is greater than 2, it behaves as a cascade of 2-input gates and performs an odd-parity function.

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
        'condition' => 'is_author',
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
        'condition' => 'is_author',
    ],
]
```

## Boolean Permissions

Boolean permissions are a special kind of permission. They can be used for allowing or disallowing access for everyone (except those with bypass access). They are not allowed as descendants to a permission type and they may not contain children. Both true booleans and booleans represented as strings are supported. Of course a simpler way to allow access to everyone is to not define any permissions at all for that action, but it might be nice sometimes to explicitly allow access for everyone.

Examples:

```php
//Allow access for anyone
[
    true,
]

//Using a boolean without an array is also permitted
true
```

```php
//Example with string representation
[
    'true',
]

//Using a string representation without an array is also permitted
'true'
```

```php
//Deny access for everyone except those with bypass access
[
    false,
]

//Using a boolean without an array is also permitted
false
```

```php
//Example with string representation
[
    'false',
]

//Using a string representation without an array is also permitted
'false'
```

```php
//Deny access for everyone including those with bypass access
[
    false,
    'NO_BYPASS' => true,
]
```
