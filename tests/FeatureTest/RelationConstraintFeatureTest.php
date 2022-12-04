<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\RelationConstraint;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class RelationConstraintFeatureTest extends FeatureTestCase
{
    public function testConstraint(): void
    {
        $relationConstraintA = (new RelationConstraint())
            ->setFor('RELATION_A')
            ->setType('NOT NULL')
            ->setName('constraint_relation_a')
            ->addProperty('id');
        $relationConstraintB = (new RelationConstraint())
            ->setFor('RELATION_B')
            ->setType('NOT NULL')
            ->setName('constraint_relation_b')
            ->addProperty('otherProperty');

        $em = $this->container->get(EntityManager::class);
        $this->assertConstraintDoesNotExist('constraint_relation_a');
        $em->create($relationConstraintA);
        $em->flush();
        $this->assertConstraintExist('constraint_relation_a');
        $this->assertConstraintDoesNotExist('constraint_relation_b');
        $em->create($relationConstraintB);
        $em->flush();
        $this->assertConstraintExist('constraint_relation_b');
        $em->delete($relationConstraintA);
        $em->flush();
        $this->assertConstraintDoesNotExist('constraint_relation_a');
        $em->delete($relationConstraintB);
        $em->flush();
        $this->assertConstraintDoesNotExist('constraint_relation_b');
    }
}
