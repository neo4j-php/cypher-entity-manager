<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\RelationIndex;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class RelationIndexFeatureTest extends FeatureTestCase
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

        $relationIndexA = (new RelationIndex())
            ->setFor('RELATION_A')
            ->setType($defaultIndex)
            ->setName('index_relation_a')
            ->addProperty('id');
        $relationIndexB = (new RelationIndex())
            ->setFor('RELATION_B')
            ->setType($defaultIndex)
            ->setName('index_relation_b')
            ->addProperty('id')
            ->addProperty('composite');

        $em = $this->container->get(EntityManager::class);
        $this->assertIndexDoesNotExist('index_relation_a');
        $em->create($relationIndexA);
        $em->flush();
        $this->assertIndexExist('index_relation_a');
        $em->create($relationIndexB);
        $em->flush();
        $this->assertIndexExist('index_relation_b');

        $em->delete($relationIndexA);
        $em->flush();
        $this->assertIndexDoesNotExist('index_relation_a');
    }
}
