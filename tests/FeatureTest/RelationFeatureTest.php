<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class RelationFeatureTest extends FeatureTestCase
{
    public function testRelation(): void
    {
        $nodeA = (new Node())
            ->addLabel('NodeA')
            ->addProperty('id', 1000)
            ->addProperty('name', 'A')
            ->addIdentifier('id');
        $nodeB = (new Node())
            ->addLabel('NodeB')
            ->addProperty('id', 1001)
            ->addProperty('name', 'B')
            ->addIdentifier('id');

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setType('RELATION')
            ->addProperty('id', 2001)
            ->addIdentifier('id');

        $em = $this->container->get(EntityManager::class);
        $this->assertNodeCount(0);
        $this->assertRelationCount(0);
        $em->create($nodeA);
        $em->create($nodeB);
        $em->create($relation);
        $em->flush();
        $this->assertNodeCount(2);
        $this->assertRelationCount(1);
        $relation->addProperty('newProperty', 'some value');
        $em->merge($relation);
        $em->flush();
        $this->assertNodeCount(2);
        $this->assertRelationCount(1);
        $em->delete($relation);
        $em->flush();
        $this->assertNodeCount(2);
        $this->assertRelationCount(0);
    }
}
