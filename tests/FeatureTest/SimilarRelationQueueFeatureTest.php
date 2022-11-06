<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;
use Syndesi\CypherEntityManager\Type\SimilarRelationQueue;

class SimilarRelationQueueFeatureTest extends FeatureTestCase
{
    public function testNode(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 1000)
            ->addProperty(new PropertyName('name'), 'a')
            ->addIdentifier(new PropertyName('id'));

        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 1001)
            ->addProperty(new PropertyName('name'), 'b')
            ->addIdentifier(new PropertyName('id'));

        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 1002)
            ->addProperty(new PropertyName('name'), 'c')
            ->addIdentifier(new PropertyName('id'));

        $nodeD = new Node();
        $nodeD
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 1003)
            ->addProperty(new PropertyName('name'), 'd')
            ->addIdentifier(new PropertyName('id'));

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC)
            ->enqueue($nodeD);

        $relationAB = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2000)
            ->addProperty(new PropertyName('name'), 'ab')
            ->addIdentifier(new PropertyName('id'));

        $relationBC = (new Relation())
            ->setStartNode($nodeB)
            ->setEndNode($nodeC)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2001)
            ->addProperty(new PropertyName('name'), 'bc')
            ->addIdentifier(new PropertyName('id'));

        $relationCD = (new Relation())
            ->setStartNode($nodeC)
            ->setEndNode($nodeD)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2002)
            ->addProperty(new PropertyName('name'), 'cd')
            ->addIdentifier(new PropertyName('id'));

        $relationDA = (new Relation())
            ->setStartNode($nodeD)
            ->setEndNode($nodeA)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2003)
            ->addProperty(new PropertyName('name'), 'da')
            ->addIdentifier(new PropertyName('id'));

        $relationAC = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeC)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2004)
            ->addProperty(new PropertyName('name'), 'ac')
            ->addIdentifier(new PropertyName('id'));

        $similarRelationQueue = (new SimilarRelationQueue())
            ->enqueue($relationAB)
            ->enqueue($relationBC)
            ->enqueue($relationAC);

        $em = $this->container->get(EntityManager::class);
        $this->assertNodeCount(0);
        $this->assertRelationCount(0);
        $em->create($similarNodeQueue);
        $em->flush();
        $this->assertNodeCount(4);
        $this->assertRelationCount(0);

        $em->create($similarRelationQueue);
        $em->flush();
        $this->assertNodeCount(4);
        $this->assertRelationCount(3);

        $relationAB->addProperty(new PropertyName('test'), 'merge');
        $relationBC->addProperty(new PropertyName('test'), 'merge');
        $relationCD->addProperty(new PropertyName('test'), 'merge');
        $relationDA->addProperty(new PropertyName('test'), 'merge');
        $relationAC->addProperty(new PropertyName('test'), 'merge');
        $similarRelationQueue = (new SimilarRelationQueue())
            ->enqueue($relationAB)
            ->enqueue($relationBC)
            ->enqueue($relationCD)
            ->enqueue($relationDA)
            ->enqueue($relationAC);

        $em->merge($similarRelationQueue);
        $em->flush();

        $this->assertNodeCount(4);
        $this->assertRelationCount(5);

        $similarRelationQueue = (new SimilarRelationQueue())
            ->enqueue($relationAB)
            ->enqueue($relationBC)
            ->enqueue($relationAC);
        $em->delete($similarRelationQueue);
        $em->flush();

        $this->assertNodeCount(4);
        $this->assertRelationCount(2);
    }
}
