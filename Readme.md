[![GitHub](https://img.shields.io/github/license/neo4j-php/cypher-entity-manager)](https://github.com/neo4j-php/cypher-entity-manager/blob/main/LICENSE)
![Neo4j Version Support](https://img.shields.io/badge/Neo4j-4.4%2B-blue)
![Packagist PHP Version Support (specify version)](https://img.shields.io/packagist/php-v/syndesi/cypher-entity-manager/dev-main)
![Packagist Version](https://img.shields.io/packagist/v/syndesi/cypher-entity-manager)
![Packagist Downloads](https://img.shields.io/packagist/dm/syndesi/cypher-entity-manager)

[![Test Coverage](https://api.codeclimate.com/v1/badges/ecd3da92ddb4d8ac99a5/test_coverage)](https://codeclimate.com/github/Syndesi/cypher-entity-manager/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/ecd3da92ddb4d8ac99a5/maintainability)](https://codeclimate.com/github/Syndesi/cypher-entity-manager/maintainability)

# Syndesi's Cypher Entity Manager

This library provides an entity manager for Cypher data types.  
This basically means, that you do not have to write create/merge/delete statements for your nodes, relations etc. per
hand. Instead, you just call `$em->create($node)`, `$em->merge($node)`, `$em->delete($node)` and at the end
`$em->flush()`.

- [Documentation](https://neo4j-php.github.io/cypher-entity-manager)
- [Packagist](https://packagist.org/packages/syndesi/cypher-entity-manager)

## Installation

To install this library, run the following code:

```bash
composer require syndesi/cypher-entity-manager
```

This is all, now you can use the library :D

## Using the library

```php
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherEntityManager\Type\EntityManager;

/**
 * note: the container should be provided by your framework. manual configuration is possible, see documentation
 * @var EntityManagerInterface $em 
 */
$em = $container->get(EntityManager::class);

$node = new Node();
$node
    ->addLabel('NodeLabel')
    ->addIdentifier('id', 123)
    ->addProperty('someProperty', 'someValue')
    ->addIdentifier('id');

// create a node:
$em->create($node);
$em->flush();

// update a node:
$node->addProperty('newProperty', 'Hello world :D');
$em->merge($node);
$em->flush();

// delete a node:
$em->delete($node);
$em->flush();
```
