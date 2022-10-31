<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;

class SimilarNodeQueueFeatureTest extends FeatureTestCase
{
    public function testNode(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1001)
            ->addProperty(new PropertyName('name'), 'a')
            ->addIdentifier(new PropertyName('identifier'));

        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1002)
            ->addProperty(new PropertyName('name'), 'b')
            ->addIdentifier(new PropertyName('identifier'));

        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1003)
            ->addProperty(new PropertyName('name'), 'c')
            ->addIdentifier(new PropertyName('identifier'));

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
            ->addProperty(new PropertyName('newProperty'), 'some value');

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
