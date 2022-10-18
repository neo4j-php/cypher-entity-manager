<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Selective\Container\Container;

class FunctionalTestCase extends ContainerTestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://neo4j:password@neo4j')
            ->build();
        $this->container->set(ClientInterface::class, $client);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
