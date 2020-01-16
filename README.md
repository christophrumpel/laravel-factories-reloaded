# Laravel Factories Reloaded

[![Latest Version on Packagist](https://img.shields.io/packagist/v/christophrumpel/laravel-factories-reloaded.svg?style=flat-square)](https://packagist.org/packages/christophrumpel/laravel-factories-reloaded)
[![Build Status](https://img.shields.io/travis/christophrumpel/laravel-factories-reloaded/master.svg?style=flat-square)](https://travis-ci.org/christophrumpel/laravel-factories-reloaded)
[![Quality Score](https://img.shields.io/scrutinizer/g/christophrumpel/laravel-factories-reloaded.svg?style=flat-square)](https://scrutinizer-ci.com/g/christophrumpel/laravel-factories-reloaded)
[![Total Downloads](https://img.shields.io/packagist/dt/christophrumpel/laravel-factories-reloaded.svg?style=flat-square)](https://packagist.org/packages/christophrumpel/laravel-factories-reloaded)

This package extends Laravel factories. Instead of using factory files, you can now generate dedicated `factory classes`.

**I see three beneftis:**

- You have dedicated class for every factory and the test data can be defined inside. This is a much cleaner solution than the default factory files.
- If creating test data is more complex than creating just one model, you hide this inside the factory class so that your tests stay clean.
- The generated factory classes use return types so that your IDE know what gets returned. (This is something you do not have with the default factory handling of Laravel.)

I already know a lot of people using factory classes. So why not just create your own classes when you need them?

- Automate everything! Even just calling an artisan command that creates a class is much faster than you doing it yourself.
- This package will create classes that already provide factory features you know like creating a new model instance or multiple ones. (States coming soon as well)


## Installation

You can install the package via composer:

```bash
composer require christophrumpel/laravel-factories-reloaded
```

## Preparation

First you need to create a new factory class. This is done via a new command this package comes with. In this example we want to create a new user factory.

``` php
php artisan make:factoryReloaded
```

After running this command, you have to select one of your models. Here you decide for which model you are creating a factory for. I will select the user model. (Through a config you can define where your models live)


![Screenshot of the command](http://screenshots.nomoreencore.com/laravel_factories_reloaded.png)

This will give you a new `UserFactory` under the `Tests\Factories` namespace. Inside this class, you can now define the properties of the model. It is very similar to what you would do with Laravel default factory.

``` php
protected method define(Faker $faker)
{
    return [
        'email' => $faker->email;
    ];
}
```

## Usage

Now you can start using your new user factory by just calling the static `create` or `createMultiple` method.

``` php
$user = UserFactory::create();
```

or


``` php
$users = UserFactory::createMultiple(5);
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email christoph@christoph-rumpel.com instead of using the issue tracker.

## Credits

- [Christoph Rumpel](https://github.com/christophrumpel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
