<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;

class SimilarNodeQueueFeatureTest extends FeatureTestCase
{
    public function testNode(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addLabel('Node')
            ->addProperty('identifier', 1001)
            ->addProperty('name', 'a')
            ->addIdentifier('identifier');

        $nodeB = new Node();
        $nodeB
            ->addLabel('Node')
            ->addProperty('identifier', 1002)
            ->addProperty('name', 'b')
            ->addIdentifier('identifier');

        $nodeC = new Node();
        $nodeC
            ->addLabel('Node')
            ->addProperty('identifier', 1003)
            ->addProperty('name', 'c')
            ->addIdentifier('identifier');

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $em = $this->container->get(EntityManager::class);
        $this->assertNodeCount(0);
        $em->create($similarNodeQueue);
        $em->flush();
        $this->assertNodeCount(3);

        $nodeB = $nodeB
            ->addProperty('newProperty', 'some value');

        $newQueue = (new SimilarNodeQueue())
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $em->merge($newQueue);
        $em->flush();
        $this->assertNodeCount(3);

        $em->delete($newQueue);
        $em->flush();
        $this->assertNodeCount(0);
    }
}
