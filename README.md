# Doctrine integration for myclabs enums

Doctrine integration for [myclabs/php-enum](https://github.com/myclabs/php-enum) via custom Doctrine types.

[![Build Status](https://img.shields.io/travis/PHP-DI/PHP-DI/master.svg?style=flat-square)](https://travis-ci.org/PHP-DI/PHP-DI)
[![Latest Version](https://img.shields.io/github/release/PHP-DI/PHP-DI.svg?style=flat-square)](https://packagist.org/packages/PHP-DI/php-di)
[![Total Downloads](https://img.shields.io/packagist/dt/PHP-DI/PHP-DI.svg?style=flat-square)](https://packagist.org/packages/PHP-DI/php-di)

This library helps you store Enums in database using Doctrine via [custom Doctrine types](https://www.doctrine-project.org/projects/doctrine-orm/en/current/cookbook/custom-mapping-types.html). Enums must be defined using [myclabs/php-enum](https://github.com/myclabs/php-enum).

## Installation

```
composer require mnapoli/enum-doctrine
```

## Usage

Given the following enum:

```php
class Currency extends Enum
{
    private const DOLLAR = 'dollar';
    private const EURO = 'euro';
}
```

You will need to write a custom Doctrine type **that inherits `StringEnumType` or `IntegerEnumType`**:

```php
class CurrencyType extends \MyCLabs\Enum\Doctrine\StringEnumType
{
    public function getName(): string
    {
        return 'currency';
    }

    public function getClassName(): string
    {
        return Currency::class;
    }
}
```

You then need to register the custom type ([see the Doctrine documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/current/cookbook/custom-mapping-types.html)):

```php
Type::addType('currency', 'App\Type\CurrencyType');
$conn = $em->getConnection();
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('db_currency', 'currency');
```

In Symfony ([see the Symfony documentation](https://symfony.com/doc/current/doctrine/dbal.html#registering-custom-mapping-types)):

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            currency: 'App\Type\CurrencyType'
```

The type can now be used in Doctrine, for example in entities:

```php
class Foo
{
    /**
     * @var Currency
     * @ORM\Column(type="currency")
     */
    private $currency;

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }
}
```

## Integer values

If your Enum uses `int` values in the database like this one:

```php
class Currency extends Enum
{
    private const DOLLAR = 1;
    private const EURO = 2;
}
```

you will need to extend `IntegerEnumType` instead:

```php
class CurrencyType extends IntegerEnumType
{
    public function getName(): string
    {
        return 'currency';
    }

    public function getClassName(): string
    {
        return Currency::class;
    }
}
```

## Custom behavior

When mapping entities to a legacy database we sometimes have to deal with weird values outside of our control. In this case feel free to override methods in the parent class.

Here is an example where we force empty strings in the database to be turned into `null` in PHP:

```php
class CurrencyType extends \MyCLabs\Enum\Doctrine\StringEnumType
{
    ...
    
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // We map the '' value (in database) to `null` in the PHP code
        if ($value === '') {
            return null;
        }

        // Everything else is handled as usual
        return parent::convertToPHPValue($value, $platform);
    }
}
```
