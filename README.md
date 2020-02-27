# Laravel Factories Reloaded

[![Latest Version on Packagist](https://img.shields.io/packagist/v/christophrumpel/laravel-factories-reloaded.svg?style=flat-square)](https://packagist.org/packages/christophrumpel/laravel-factories-reloaded)
[![Build Status](https://img.shields.io/travis/christophrumpel/laravel-factories-reloaded/master.svg?style=flat-square)](https://travis-ci.org/christophrumpel/laravel-factories-reloaded)
[![Quality Score](https://img.shields.io/scrutinizer/g/christophrumpel/laravel-factories-reloaded.svg?style=flat-square)](https://scrutinizer-ci.com/g/christophrumpel/laravel-factories-reloaded)
[![Total Downloads](https://img.shields.io/packagist/dt/christophrumpel/laravel-factories-reloaded.svg?style=flat-square)](https://packagist.org/packages/christophrumpel/laravel-factories-reloaded)

This package will provide a new way to use factories in Laravel. Instead of using factory files, you can now generate dedicated `factory classes`.

**There are three benefits:**

- You have a dedicated class for every factory, and the test data can be defined inside. This is a much cleaner solution than the default factory files.
- If creating test data gets more complicated than creating just one model, you hide this inside the factory class so that your tests stay clean.
- The generated factory classes use return types so that your IDE know what gets returned. (This is something you do not have with the default factory handling of Laravel.)

I already know a lot of people using factory classes. So why not just create your own classes when you need them?

- Automate everything! Even just calling an artisan command that creates a class is much faster than you doing it yourself.
- This package will create classes that already provide factory features, you know, like creating a new model instance or multiple ones.


## Installation

You can install the package via composer:

```bash
composer require christophrumpel/laravel-factories-reloaded
```

To publish the config file run:

```bash
php artisan vendor:publish --provider="Christophrumpel\LaravelFactoriesReloaded\LaravelFactoriesReloadedServiceProvider"
```

It will provide the package's config file where you can define the `path of your models`, the `path of the generated factories`, as well as the `generated factories namespace`.

```php
<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'models_path' => base_path('app'),

    'factories_path' => base_path('tests/Factories'),

    'factories_namespace' => 'Tests\Factories',
];
```

## Preparation

First, you need to create a new factory class. This is done via a new command this package comes with. In this example, we want to create a new user factory.

```php
php artisan make:factory-reloaded
```

After running this command, you have to select one of your models. Here you decide for which model you are creating a factory for. I will choose the user model. (Through a config you can define where your models live)


![Screenshot of the command](http://screenshots.nomoreencore.com/laravel_factories_reloaded_pick_v3.png)

This will give you a new `UserFactory` under the `Tests\Factories` namespace. Here is your new basic factory class:

```php
class UserFactory extends BaseFactory
{

    protected string $modelClass = User::class;

    public function create(array $extra = []): User
    {
        return parent::build($extra);
    }
    
    public function make(array $extra = []): User
    {
        return parent::build($extra, 'make');
    }

    public function getData(Generator $faker): array
    {
        return [];
    }

}
```

Inside this class, you can define the properties of the model with the `getData` method. It is very similar to what you would do with a Laravel default factory, and you can make use of Faker as well. The `create` method is only a copy of the one in the parent class `BaseFactory`. Still, we need it in our dedicated factory class so that we can define what gets returned. In our case, it is a user model. Other methods like `new` or `times` are hidden in the parent class.


## Usage

Now you can start using your new user factory class in your tests. The static `new` method gives you a new instance of the factory. This is useful to chain other methods, like `create` for example.

``` php
$user = UserFactory::new()->create();
```

This will give you back a newly created user instance from the database. If you want to create multiple instances, you can use the `times` method, which will use the `create` method behind the scenes and will return you a collection of the new model instances.

``` php
$user = UserFactory::new()
    ->times(4)
    ->create();
```

Like with Laravel factories you can also `make` a new model which gets `not` stored to the database yet.

``` php
$user = UserFactory::new()->make();
```

### Relations

There will be situations when you need to add related models to your test data. This is already pretty easy with using multiple factory classes.

```php
$user = UserFactory::new()->create();
$user->recipes()->saveMany(RecipeFactory::new()->times(4)->create());
```

Of course, the relations need to be set up before. Besides this, there is also an in-built solution.

```php
$user = UserFactory::new()
    ->with(Recipe::class, 'recipes')
    ->create();
```

With the `with` method, you can easily add relations in a more fluently way. The first parameter defines the mode and the second one the name of the relationship. If you need to add more than one related model, you can add a third argument to define the count.

```php
$user = UserFactory::new()
    ->with(Recipe::class, 'recipes', 4)
    ->create();
```

> :warning: **Note**: For this to work, you need to have created a RecipeFactory before.

But there is one more way to add related data. Since you own your factory classes, you can add a method on the class itself. This way you can pick a much more expressive name like `withRecipes`.

```php
$user = UserFactory::new()
    ->withRecipes(4)
    ->create();
```

This way you can define yourself how to set up recipes and you are more flexible doing it. Here is an example of how your `UserFactory` could look like with a custom relation method.

```php
class UserFactory extends BaseFactory
{

    protected string $modelClass = User::class;

    /** @var Collection */
    private $recipes;

    public function create(array $extra = []): User
    {
        $user = parent::create($extra);

        if ($this->recipes) {
            $user->recipes()->saveMany($this->recipes);
        }

        return $user;
    }

    public function withRecipes(int $times = 1)
    {
        $this->recipes = RecipeFactory::new()
            ->times($times)->make();

        return $this;
    }

    public function getData(Generator $faker): array
    {
        return [
            'name' => $faker->name,
            'email' => 'test@email.at',
            'password' => bcrypt('test'),
        ];
    }

}
```



### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security-related issues, please email christoph@christoph-rumpel.com instead of using the issue tracker.

## Credits

- [Christoph Rumpel](https://github.com/christophrumpel)
- [All Contributors](../../contributors)

The current implementation was improved with help from [Brent's article](https://stitcher.io/blog/laravel-beyond-crud-09-test-factories) about how they deal with factories at Spatie.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
