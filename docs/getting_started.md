# Getting Started

## Requirements

PHP: 8.1+ due to [enums](https://stitcher.io/blog/php-enums) and
[readonly class properties](https://stitcher.io/blog/php-81-readonly-properties).  
Neo4j: 4.4, 5.1 or newer.

!> Note: While this library technically works with older Neo4j versions supported by
[laudis/neo4j-php-client](https://github.com/neo4j-php/neo4j-php-client), we don't guarantee that every feature works as
expected.  
It is known that indices are problematic at Neo4j <= 4.2 and constraints at Neo4j <= 4.3 due to different syntax and
capabilities.

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
