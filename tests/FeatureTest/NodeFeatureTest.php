<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class NodeFeatureTest extends FeatureTestCase
{
    public function testNode(): void
    {
        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1236)
            ->addProperty(new PropertyName('someKey'), 'some value')
            ->addProperty(new PropertyName('otherPropertyName'), 'some value')
            ->addIdentifier(new PropertyName('identifier'));

        $em = $this->container->get(EntityManager::class);
        $this->assertNodeCount(0);
        $em->create($nodeC);
        $em->flush();
        $this->assertNodeCount(1);

        $nodeC->addProperty(new PropertyName('changed'), 'hello world update :D');

        $em->merge($nodeC);
        $em->flush();
        $this->assertNodeCount(1);

        $em->delete($nodeC);
        $em->flush();
        $this->assertNodeCount(0);
    }
}
