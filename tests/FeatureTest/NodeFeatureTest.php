<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class NodeFeatureTest extends FeatureTestCase
{
    public function testNode(): void
    {
        $nodeC = new Node();
        $nodeC
            ->addLabel('Node')
            ->addProperty('identifier', 1236)
            ->addProperty('someKey', 'some value')
            ->addProperty('otherPropertyName', 'some value')
            ->addIdentifier('identifier');

        $em = $this->container->get(EntityManager::class);
        $this->assertNodeCount(0);
        $em->create($nodeC);
        $em->flush();
        $this->assertNodeCount(1);

        $nodeC->addProperty('changed', 'hello world update :D');

        $em->merge($nodeC);
        $em->flush();
        $this->assertNodeCount(1);

        $em->delete($nodeC);
        $em->flush();
        $this->assertNodeCount(0);
    }
}
