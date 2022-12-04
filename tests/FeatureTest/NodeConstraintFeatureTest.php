<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\NodeConstraint;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class NodeConstraintFeatureTest extends FeatureTestCase
{
    public function testConstraint(): void
    {
        $nodeConstraintA = (new NodeConstraint())
            ->setFor('NodeA')
            ->setType('UNIQUE')
            ->setName('constraint_node_a')
            ->addProperty('id');
        $nodeConstraintB = (new NodeConstraint())
            ->setFor('NodeB')
            ->setType('UNIQUE')
            ->setName('constraint_node_b')
            ->addProperty('id')
            ->addProperty('otherProperty');

        $em = $this->container->get(EntityManager::class);
        $this->assertConstraintDoesNotExist('constraint_node_a');
        $em->create($nodeConstraintA);
        $em->flush();
        $this->assertConstraintExist('constraint_node_a');
        $this->assertConstraintDoesNotExist('constraint_node_b');
        $em->create($nodeConstraintB);
        $em->flush();
        $this->assertConstraintExist('constraint_node_b');
        $em->delete($nodeConstraintA);
        $em->flush();
        $this->assertConstraintDoesNotExist('constraint_node_a');
        $em->delete($nodeConstraintB);
        $em->flush();
        $this->assertConstraintDoesNotExist('constraint_node_b');
    }
}
