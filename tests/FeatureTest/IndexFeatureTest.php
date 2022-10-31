<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Index;
use Syndesi\CypherDataStructures\Type\IndexName;
use Syndesi\CypherDataStructures\Type\IndexType;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class IndexFeatureTest extends FeatureTestCase
{
    public function testIndex(): void
    {
        $nodeIndexA = (new Index())
            ->setFor(new NodeLabel('NodeA'))
            ->setIndexType(IndexType::BTREE)
            ->setIndexName(new IndexName('index_node_a'))
            ->addProperty(new PropertyName('id'));
        $nodeIndexB = (new Index())
            ->setFor(new NodeLabel('NodeB'))
            ->setIndexType(IndexType::BTREE)
            ->setIndexName(new IndexName('index_node_b'))
            ->addProperty(new PropertyName('id'))
            ->addProperty(new PropertyName('composite'));
        $relationIndex = (new Index())
            ->setFor(new RelationType('RELATION'))
            ->setIndexType(IndexType::BTREE)
            ->setIndexName(new IndexName('index_relation'))
            ->addProperty(new PropertyName('id'));

        $em = $this->container->get(EntityManager::class);
        $this->assertIndexDoesNotExist('index_node_a');
        $em->create($nodeIndexA);
        $em->flush();
        $this->assertIndexExist('index_node_a');
        $em->create($nodeIndexB);
        $em->flush();
        $this->assertIndexExist('index_node_b');
        $em->create($relationIndex);
        $em->flush();
        $this->assertIndexExist('index_relation');

        $em->delete($nodeIndexA);
        $em->flush();
        $this->assertIndexDoesNotExist('index_node_a');

        $em->delete($relationIndex);
        $em->flush();
        $this->assertIndexDoesNotExist('relation_index');
    }
}
