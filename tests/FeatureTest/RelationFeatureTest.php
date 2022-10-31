<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class RelationFeatureTest extends FeatureTestCase
{
    public function testRelation(): void
    {
        $nodeA = (new Node())
            ->addNodeLabel(new NodeLabel('NodeA'))
            ->addProperty(new PropertyName('id'), 1000)
            ->addProperty(new PropertyName('name'), 'A')
            ->addIdentifier(new PropertyName('id'));
        $nodeB = (new Node())
            ->addNodeLabel(new NodeLabel('NodeB'))
            ->addProperty(new PropertyName('id'), 1001)
            ->addProperty(new PropertyName('name'), 'B')
            ->addIdentifier(new PropertyName('id'));

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2001)
            ->addIdentifier(new PropertyName('id'));

        $em = $this->container->get(EntityManager::class);
        $this->assertNodeCount(0);
        $this->assertRelationCount(0);
        $em->create($nodeA);
        $em->create($nodeB);
        $em->create($relation);
        $em->flush();
        $this->assertNodeCount(2);
        $this->assertRelationCount(1);
        $relation->addProperty(new PropertyName('newProperty'), 'some value');
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
