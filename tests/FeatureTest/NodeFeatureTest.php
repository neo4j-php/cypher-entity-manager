<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Laudis\Neo4j\Databags\Statement;
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
        $this->assertSame(
            0,
            $em->getClient()->runStatement(Statement::create("MATCH (n) RETURN count(n)"))->get(0)->get('count(n)')
        );
        $em->create($nodeC);
        $em->flush();
        $this->assertSame(
            1,
            $em->getClient()->runStatement(Statement::create("MATCH (n) RETURN count(n)"))->get(0)->get('count(n)')
        );

        $nodeC->addProperty(new PropertyName('changed'), 'hello world update :D');

        $em->merge($nodeC);
        $em->flush();
        $this->assertSame(
            1,
            $em->getClient()->runStatement(Statement::create("MATCH (n) RETURN count(n)"))->get(0)->get('count(n)')
        );

        $em->delete($nodeC);
        $em->flush();
        $this->assertSame(
            0,
            $em->getClient()->runStatement(Statement::create("MATCH (n) RETURN count(n)"))->get(0)->get('count(n)')
        );
    }
}
