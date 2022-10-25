<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\Statement;
use Selective\Container\Container;
use Syndesi\CypherEntityManager\Type\EntityManager;

class FeatureTestCase extends ContainerTestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        if (!getenv('ENABLE_FEATURE_TEST')) {
            $this->markTestSkipped();
        }
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://neo4j:password@neo4j')
            ->build();
        $client->runStatement(Statement::create("MATCH (n) DETACH DELETE n"));
        $this->container->set(ClientInterface::class, $client);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function assertNodeCount(int $expectedCount): void
    {
        $em = $this->container->get(EntityManager::class);
        $client = $em->getClient();
        $count = $client->runStatement(Statement::create("MATCH (n) RETURN count(n)"))->get(0)->get('count(n)');
        $this->assertSame($expectedCount, $count, "Node count does not match.");
    }
}
