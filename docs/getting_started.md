# Getting Started

## Requirements

PHP: 8.1+ due to [enums](https://stitcher.io/blog/php-enums) and
[readonly class properties](https://stitcher.io/blog/php-81-readonly-properties).

## Installation

Install the package from [Packagist](https://packagist.org/packages/syndesi/cypher-entity-manager) by executing the
following command:

```bash
composer require syndesi/cypher-entity-manager
```

It is also recommended to install the following libraries explicitly:

```bash
composer require laudis/neo4j-php-client
composer require syndesi/cypher-data-structures
```

## Configuration

In case you are using one of the supported frameworks, you can install their bridge bundles.

### Symfony

```bash
composer require syndesi/cypher-entity-manager-bridge-symfony
```

### Laravel

```bash
composer require syndesi/cypher-entity-manager-bridge-laravel
```

### Manual

If you are not using one of the supported frameworks or want to use this library manually, follow these steps:

1. Enable the event listeners inside `/src/EventListener`.
2. Create an instance of `Syndesi\CypherEntityManager\Type\EntityManager`. The required event dispatcher must be able to
   reach the enabled event listeners from step 1.
