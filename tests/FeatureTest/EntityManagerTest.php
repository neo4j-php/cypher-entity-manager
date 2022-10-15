<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Laudis\Neo4j\ClientBuilder;
use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherEntityManager\Type\EntityManager;

class EntityManagerTest extends TestCase
{

    public function testEntityManager(): void
    {
        $client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://neo4j:password@neo4j')
            ->build();
        $em = new EntityManager($client);


        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('NodeA'))
            ->addProperty(new PropertyName('id'), 1234)
            ->addProperty(new PropertyName('someKey'), 'some value')
            ->addIdentifier(new PropertyName('id'));
        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('NodeB'))
            ->addProperty(new PropertyName('id'), 1235)
            ->addProperty(new PropertyName('someKey'), 'some value')
            ->addIdentifier(new PropertyName('id'));
        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('NodeC'))
            ->addNodeLabel(new NodeLabel('AnotherNodeLabel'))
            ->addProperty(new PropertyName('identifier'), 1236)
            ->addProperty(new PropertyName('someKey'), 'some value')
            ->addProperty(new PropertyName('otherPropertyName'), 'some value')
            ->addIdentifier(new PropertyName('identifier'));

        $em
            ->create($nodeA)
            ->create($nodeB)
            ->create($nodeC)
            ->flush();

        $this->assertTrue(true);
    }

}
