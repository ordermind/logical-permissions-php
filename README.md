<a href="https://travis-ci.org/ordermind/logical-permissions-php" target="_blank"><img src="https://travis-ci.org/ordermind/logical-permissions-php.svg?branch=3.x" /></a>
# logical-permissions

This is a generic library that provides support for array-based permissions with logic gates such as AND and OR. You can register permission checkers for any kind of permission types such as roles and flags. The idea with this library is to be an ultra-flexible foundation that can be used by any framework.

## Getting started

### Installation

`composer require ordermind/logical-permissions`

### Usage

#### Register permission checkers

Permission checkers are used to check different kinds of conditions for access control, and the first thing to do is to create one of these and register it. Let's say, for example, that we want to determine access using the current user's roles. First you create a class that implements ```Ordermind\LogicalPermissions\PermissionCheckerInterface``` like this:

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
use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;
use Ordermind\LogicalPermissions\Factories\DefaultFullPermissionTreeDeserializerFactory;

$fullTreeDeserializerFactory = new DefaultFullPermissionTreeDeserializerFactory();
$fullTreeDeserializer = $fullTreeDeserializerFactory->create(new MyPermissionChecker());
```
#### Check access

Now everything is set and you can check the access for a user based on their roles:
```php
$permissions = [
    'role' => 'admin', // The key 'role' here is the permission type that you return the getName() method of your permission checker
];
$fullPermissionTree = $fullTreeDeserializer->deserialize($permissions);

$user = ['roles' => ['admin', 'sales']];

$accessChecker = new AccessChecker();
$access = $accessChecker->checkAccess($fullPermissionTree, ['user' => $user]);
// true
```

### Permission trees
In the previous example, we had a variable called ```$permissions``` that looked like this:
```php
$permissions = [
    'role' => 'admin',
];
```
This is an example of a **permission tree**. A permission tree is a hierarchical combination of permissions that is evaluated in order to determine access for a specific action. Let's say for example that you want to restrict access for updating a user. You'd like only users with the role "admin" to be able to update any user, but users should also be able to update their own user data (or at least some of it). With the format that this library provides, these conditions could be expressed elegantly in a permission tree as such:

```php
[
    'OR' => [
        'role' => 'admin',
        'flag' => 'is_author',
    ],
]
```

In this example `role` and `flag` are the evaluated permission types. For this example to work you will need to register the permission types 'role' and 'flag' according to the previous guide.

### Bypassing access checks
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
use Ordermind\LogicalPermissions\AccessChecker\AccessChecker;

$accessChecker = new AccessChecker(new MyBypassAccessChecker());
```
From now on, every time you call ```$accessChecker->checkAccess()``` the user with the id 1 will be exempted so that no matter what the permissions are, they will always be granted access. If you want to make exceptions, you can do so by adding `'no_bypass' => true` to the first level of a permission tree. You can even use permissions as conditions for `no_bypass`.

Examples:

```php
//Disallow access bypassing
[
    'no_bypass' => true,
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
    'no_bypass' => true,
]
```
