<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
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
            ->addLabel('Node')
            ->addProperty('id', 1000)
            ->addProperty('name', 'a')
            ->addIdentifier('id');

        $nodeB = new Node();
        $nodeB
            ->addLabel('Node')
            ->addProperty('id', 1001)
            ->addProperty('name', 'b')
            ->addIdentifier('id');

        $nodeC = new Node();
        $nodeC
            ->addLabel('Node')
            ->addProperty('id', 1002)
            ->addProperty('name', 'c')
            ->addIdentifier('id');

        $nodeD = new Node();
        $nodeD
            ->addLabel('Node')
            ->addProperty('id', 1003)
            ->addProperty('name', 'd')
            ->addIdentifier('id');

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC)
            ->enqueue($nodeD);

        $relationAB = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setType('RELATION')
            ->addProperty('id', 2000)
            ->addProperty('name', 'ab')
            ->addIdentifier('id');

        $relationBC = (new Relation())
            ->setStartNode($nodeB)
            ->setEndNode($nodeC)
            ->setType('RELATION')
            ->addProperty('id', 2001)
            ->addProperty('name', 'bc')
            ->addIdentifier('id');

        $relationCD = (new Relation())
            ->setStartNode($nodeC)
            ->setEndNode($nodeD)
            ->setType('RELATION')
            ->addProperty('id', 2002)
            ->addProperty('name', 'cd')
            ->addIdentifier('id');

        $relationDA = (new Relation())
            ->setStartNode($nodeD)
            ->setEndNode($nodeA)
            ->setType('RELATION')
            ->addProperty('id', 2003)
            ->addProperty('name', 'da')
            ->addIdentifier('id');

        $relationAC = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeC)
            ->setType('RELATION')
            ->addProperty('id', 2004)
            ->addProperty('name', 'ac')
            ->addIdentifier('id');

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

        $relationAB->addProperty('test', 'merge');
        $relationBC->addProperty('test', 'merge');
        $relationCD->addProperty('test', 'merge');
        $relationDA->addProperty('test', 'merge');
        $relationAC->addProperty('test', 'merge');
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
