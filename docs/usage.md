# Usage

?> Note: It is assumed that you use either one of the supported frameworks (Symfony, Laravel) or a framework which
supports [PSR-11](https://www.php-fig.org/psr/psr-11/).

This library uses three main actions:

* Create: Creates new objects, can lead to duplicate objects if not careful.  
  Has a better performance than merge.
* Merge: If an existing object with same labels & identifiers is found, merge new properties with it. If no matching
  object is found, create it.
* Delete: Removes an object from the database.

!> Not all objects support the merge operation, e.g. indices and constraints.

This library also adds two new Cypher data structure elements which provide the entity manager the opportunity to
optimize statements. In some cases they are automatically used:

* SimilarNodeQueue: A queue or list of nodes which have the same labels and identifier names.
* SimilarRelationQueue: A queue or list of relations which have the same labels, identifier names and similar start- &
  end nodes.

## Events

There are two types of events dispatched by this library: Lifecycle events and statement generation events.

### Lifecycle events

Lifecycle events are dispatched for every object before & after their Cypher statements are sent to the database.

### Statement generation events

Statement generation events are internally used to create Cypher statements from
`ActionCypherElementInterface`-objects.  
The generated statements are optimized and tested for [Neo4j](https://neo4j.com/), support for
[Memgraph](https://memgraph.com/) is planned.

If you need to support other graph databases, you usually just need to create custom event handlers with a higher
priority.

## Creating objects

```php
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\PropertyName;

/** @var EntityManagerInterface $em */
$em = $container->get(EntityManagerInterface::class);

$node = new Node();
$node
    ->addProperty('id', 1234)
    ->addIdentifier('id');

$em->create($node);
$em->flush();
```

## Merging objects

```php
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\PropertyName;

/** @var EntityManagerInterface $em */
$em = $container->get(EntityManagerInterface::class);

$node = new Node();
$node
    ->addProperty('id', 1234)
    ->addProperty('newProperty', ':D')
    ->addIdentifier('id');

$em->merge($node);
$em->flush();
```

## Deleting objects

```php
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\PropertyName;

/** @var EntityManagerInterface $em */
$em = $container->get(EntityManagerInterface::class);

$node = new Node();
$node
    ->addProperty('id', 1234)
    ->addIdentifier('id');

$em->delete($node);
$em->flush();
```
