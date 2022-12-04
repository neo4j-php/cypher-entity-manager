<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\NodeIndex;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class NodeIndexFeatureTest extends FeatureTestCase
{
    public function testIndex(): void
    {
        // use BTREE for Neo4j 4.x and RANGE for Neo4j 5.x
        $defaultIndex = 'BTREE';
        if (false !== $_ENV["NEO4J_VERSION"]) {
            if (str_starts_with($_ENV["NEO4J_VERSION"], '5.')) {
                $defaultIndex = 'RANGE';
            }
        }

        $nodeIndexA = (new NodeIndex())
            ->setFor('NodeA')
            ->setType($defaultIndex)
            ->setName('index_node_a')
            ->addProperty('id');
        $nodeIndexB = (new NodeIndex())
            ->setFor('NodeB')
            ->setType($defaultIndex)
            ->setName('index_node_b')
            ->addProperty('id')
            ->addProperty('composite');

        $em = $this->container->get(EntityManager::class);
        $this->assertIndexDoesNotExist('index_node_a');
        $em->create($nodeIndexA);
        $em->flush();
        $this->assertIndexExist('index_node_a');
        $em->create($nodeIndexB);
        $em->flush();
        $this->assertIndexExist('index_node_b');

        $em->delete($nodeIndexA);
        $em->flush();
        $this->assertIndexDoesNotExist('index_node_a');
    }
}
