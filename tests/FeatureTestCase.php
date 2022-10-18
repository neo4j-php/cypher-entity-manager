<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\Statement;
use Selective\Container\Container;

class FeatureTestCase extends ContainerTestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://neo4j:neo4j@neo4j')
            ->build();
        $client->runStatement(Statement::create("MATCH (n) DETACH DELETE n"));
        $this->container->set(ClientInterface::class, $client);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
