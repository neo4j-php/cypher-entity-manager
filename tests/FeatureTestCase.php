<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests;

use Dotenv\Dotenv;
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

        $dotenv = Dotenv::createImmutable(__DIR__."/../");
        $dotenv->safeLoad();

        if (!array_key_exists('ENABLE_FEATURE_TEST', $_ENV)) {
            $this->markTestSkipped();
        }
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        $client = ClientBuilder::create()
            ->withDriver('bolt', $_ENV['NEO4J_AUTH'])
            ->build();
        $client->runStatement(Statement::create("MATCH (n) DETACH DELETE n"));
        $this->container->set(ClientInterface::class, $client);
        $this->deleteAllConstraintsAndIndexes();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function deleteAllConstraintsAndIndexes(): void
    {
        $client = $this->container->get(ClientInterface::class);
        $indexes = $client->runStatement(Statement::create("SHOW CONSTRAINTS"));
        foreach ($indexes->toArray() as $index) {
            $client->runStatement(Statement::create(sprintf(
                "DROP CONSTRAINT %s IF EXISTS",
                $index['name']
            )));
        }
        $indexes = $client->runStatement(Statement::create("SHOW INDEXES"));
        foreach ($indexes->toArray() as $index) {
            $client->runStatement(Statement::create(sprintf(
                "DROP INDEX %s IF EXISTS",
                $index['name']
            )));
        }
    }

    public function assertNodeCount(int $expectedCount): void
    {
        $em = $this->container->get(EntityManager::class);
        $client = $em->getClient();
        $count = $client->runStatement(Statement::create("MATCH (n) RETURN count(n)"))->get(0)->get('count(n)');
        $this->assertSame($expectedCount, $count, "Node count does not match.");
    }

    public function assertRelationCount(int $expectedCount): void
    {
        $em = $this->container->get(EntityManager::class);
        $client = $em->getClient();
        $count = $client->runStatement(Statement::create("MATCH ()-[r]-() RETURN count(DISTINCT r)"))->get(0)->get('count(DISTINCT r)');
        $this->assertSame($expectedCount, $count, "Relation count does not match.");
    }

    public function assertIndexExist(string $name): void
    {
        $em = $this->container->get(EntityManager::class);
        $client = $em->getClient();
        $count = $client->runStatement(Statement::create(sprintf(
            "SHOW INDEXES WHERE name = \"%s\"",
            $name
        )))->count();
        $this->assertSame(1, $count, sprintf("Index with name %s does not exist", $name));
    }

    public function assertIndexDoesNotExist(string $name): void
    {
        $em = $this->container->get(EntityManager::class);
        $client = $em->getClient();
        $count = $client->runStatement(Statement::create(sprintf(
            "SHOW INDEXES WHERE name = \"%s\"",
            $name
        )))->count();
        $this->assertSame(0, $count, sprintf("Index with name %s exists but is expected to be missing", $name));
    }

    public function assertConstraintExist(string $name): void
    {
        $em = $this->container->get(EntityManager::class);
        $client = $em->getClient();
        $count = $client->runStatement(Statement::create(sprintf(
            "SHOW CONSTRAINT WHERE name = \"%s\"",
            $name
        )))->count();
        $this->assertSame(1, $count, sprintf("Constraint with name %s does not exist", $name));
    }

    public function assertConstraintDoesNotExist(string $name): void
    {
        $em = $this->container->get(EntityManager::class);
        $client = $em->getClient();
        $count = $client->runStatement(Statement::create(sprintf(
            "SHOW CONSTRAINT WHERE name = \"%s\"",
            $name
        )))->count();
        $this->assertSame(0, $count, sprintf("Constraint with name %s exists but is expected to be missing", $name));
    }
}
