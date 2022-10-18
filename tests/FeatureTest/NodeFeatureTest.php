<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherEntityManager\Tests\FunctionalTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class NodeFeatureTest extends FunctionalTestCase
{
    public function testLol(): void
    {
        $this->assertTrue(true);

        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('ContainerTest'))
            ->addProperty(new PropertyName('identifier'), 1236)
            ->addProperty(new PropertyName('someKey'), 'some value')
            ->addProperty(new PropertyName('otherPropertyName'), 'some value')
            ->addIdentifier(new PropertyName('identifier'));

        $em = $this->container->get(EntityManager::class);
        $em->create($nodeC);
        $em->flush();

        $nodeC->addProperty(new PropertyName('changed'), 'hello world update :D');

        $em->merge($nodeC);
        $em->flush();

//        $em->delete($nodeC);
//        $em->flush();
    }
}
