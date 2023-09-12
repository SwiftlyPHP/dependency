# Swiftly - Dependency


[![PHP Version](https://img.shields.io/badge/php->=7.4-blue)](https://www.php.net/supported-versions)
[![CircleCI](https://circleci.com/gh/SwiftlyPHP/dependency/tree/main.svg?style=shield)](https://circleci.com/gh/SwiftlyPHP/dependency/tree/main)
[![Coverage Status](https://coveralls.io/repos/github/SwiftlyPHP/dependency/badge.svg?branch=main)](https://coveralls.io/github/SwiftlyPHP/dependency?branch=main)

Construct object hierarchies with ease.

Designed to be used primarily for [SwiftlyPHP](https://github.com/SwiftlyPHP)
projects, this component provides a lightweight implementation of a dependency
container and injector, allowing you to easily configure and build complex
object trees with minimal fuss.

While not as fully-featured as some containers such as Symfony's or Laravel's,
this component offers a minimal, easy to use interface for managing
dependencies.

## Installation

To install the library use [Composer](https://getcomposer.org/):

```sh
composer require swiftly/dependency
```

## Usage
### The Basics

If you've ever worked on a sizeable project, you will know the benefit of
splitting your code into well-delineated classes. While this bears a practical
benefit in making the filesystem (and project structure) cleaner, it also allows
us to separate out related areas of concern, grouping related functionality
together in a way that is easy to reason about and is composable.

Over time however, you'll likely end up with a lot of classes. Objects will
start to rely on other objects. You end up with constructors that require two or
three objects, whose own constructors take the same again, quickly spirally into
a hierarchy that is complicated to manage.

Enter the service container:

```php
<?php

use Swiftly\Dependency\Container;

$container = new Container();
```

The service container is a registry into which you can enter details about the
classes in your application.

For example, let's say we have a class like the following:

```php
<?php // MyClass.php

class MyClass
{
    public function __construct(
        private string $name,
        private int $age
    ) {}

    public function speak()
    {
        return "Hi, my name is " . $this->name . " and I am " . $this->age;
    }
}
```

We can register it with the container like so:

```php
<?php

use Swiftly\Dependency\Container;

$container = new Container();
$container->register(MyClass::class)
    ->setArguments([
        'name' => 'John',
        'age' => 42
    ]);
```

Here we've let the container know about our custom class and also provided the
arguments required to construct it. Now, when we need an instance of `MyClass`
we can call the `get()` method on the container and a copy will be created for
us.

```php
<?php
// ... continued from above ...

$myclass = $container->get(MyClass::class);

// "Hi, my name is John and I am 42"
echo $myclass->speak();
```

The benefit of this is that our object is only constructed when we explicitly
ask for it. If we never request it from the container it is never created,
saving on potentially expensive instantiation.

### Object Hierarchies

Now that we've squared away the basics, let's see how we can use the container
to make creating object hierarchies easier.

Imagine the following classes:

```php
<?php // classes.php

class Person
{
    public function __construct(
        public string $name
    ) {}
}

class Salary
{
    public function __construct(
        public int $yearly
    ) {}
}

class Job
{
    public function __construct(
        public string $title,
        protected Salary $salary
    ) {}
}

class Employee
{
    public function __construct(
        Person $person,
        Job $role
    ) {}

    public function speak()
    {
        return "I'm " . $this->person->name ", I am a " . $this->job->title;
    }
}
```

Here we have 4 classes, where the construction of `Employee` requires both a
reference to a `Person` and a `Job`, and `Job` has it's own dependency in the
form of a `Salary` object.

Traditionally constructing an `Employee` would require something like the
following: 

```php
<?php

$person = new Person("Jim");
$salary = new Salary(42_000);
$job = new Job("Developer", $salary);
$employee = new Employee($person, $job);

// "I'm Jim, I am a Developer"
echo $employee->speak();
```

With our container it would look like:

```php
<?php

use Swiftly\Dependency\Container;

$container = new Container();
$container->register(Person::class)->setArguments(['name' => 'Jim']);
$container->register(Salary::class)->setArguments(['yearly' => 42_000]);
$container->register(Job::class)->setArguments(['title' => 'Developer']);
$container->register(Employee:class);

$employee = $container->get(Employee:class);

// "I'm Jim, I am a Developer"
echo $employee->speak();
```

Instead of having to construct a new hierarchy every time we want an instance of
`Employee` we simply register our classes once and then call `get()` when
needed. The container inspects the constructors of each class, going down the 
hierarchy as far as necessary and resolving each of their requirements if it is
able to do so. (Or throwing a helpful exception if not).

### Factories

Sometimes when creating an object you may need to perform some additional setup,
such as opening a database connection or reading a config file. This is where
factories come in.

At their core, factories are just functions that create and return objects.

```php
<?php

use Swiftly\Dependency\Container;

$container = new Container();
$container->register(Person::class, function () {
    return new Person("Jill");
});

$person = $container->get(Person::class);

// "Jill"
echo $person->name;
```

This code is functionality equivalent to the `Person` examples above, but
instead of using the `setArguments()` utility the values have been hardcoded in
the factory.

A more practical example however might look like:

```php
<?php

use Swiftly\Dependency\Container;

$container = new Container();
$container->register(Database::class, function () {
    $database = new Database(...);
    $database->open();
    return $database;
});
```

Now whenever we `get()` a copy of `Database` we can be confident it is already
open and ready for use.

Great! But what if your factory also needs values passed into it?

```php
<?php

use Swiftly\Dependency\Container;

$container = new Container();
$container->register(HttpTransport::class, function (string $user_agent) {
    new CurlTransport($user_agent);
})->setArguments([
    'user_agent' => 'PHP/Curl'
]);
$container->register(
    HttpClient::class,
    function (HttpTransport $transport) {
        $client = new HttpClient($transport);
        $client->setTimeout(2000);
        $client->setBlocking(true);
        return $client;
    }
);
```

Here we've uncovered 2 key points:
1. Arguments you provide to `setArguments()` are forwarded to the factory
2. The container will resolve [type hinted](https://www.php.net/manual/en/language.types.declarations.php)
    factory arguments where possible.

### Tagging

Tagging allows you to apply custom string tags to a service, letting you collate
all services that have certain tags applied using the `tagged()` method.

Let's say you have a series of task objects, each one implementing a
`TaskInterface` as below:

```php
<?php // tasks.php

interface TaskInterface
{
    public function execute(): void;
}

class EmailTask implements TaskInterface
{
    public function execute(): void
    {
        // ... send an email
    }
}

class NotificationTask implements TaskInterface
{
    public function execute(): void
    {
        // ... send push notification
    }
}

class LogTask implements TaskInterface
{
    public function execute(): void
    {
        // ... write log data
    }
}
```

As developers we can see that these classes are related, and that each conforms
to the `TaskInterface`. Calling `execute()` on each one would run the relevant
logic. The container however is blind to this relation.

To group them together we can use tags:

```php
<?php

use Swiftly\Dependency\Container;

$container = new Container();
$container->register(EmailTask::class)
    ->setTags(['task']);
$container->register(NotificationTask::class)
    ->setTags(['task']);
$container->register(LogTask::class)
    ->setTags(['task']);

foreach ($container->tagged('task') as $task) {
    $task->execute();
}
```

Here we've done a few things:
1. We've registered our email, notification and log task classes with the
    container
2. Added our custom `task` tag to each one
3. Called the `tagged()` method, which returns all classes with a given tag

For those of you interested in type-safety you can also pass a type constraint
as the second argument, allowing you to ensure each of the tagged services is of
a given class (or implements a given interface). This also nets you a nice
positive in that it will be detected by your IDE for autocompletion, as well as
static analysis tools such as [Psalm](https://psalm.dev/) or
[PHPStan](https://phpstan.org/).

```php
<?php

foreach ($container->tagged('task', TaskInterface::class) as $task) {
    // IDE and static analysis can now infer type of `$task`
    $task->execute();
}
```

If any of the returned services don't match `TaskInterface` an exception will be
thrown explaining as much.

