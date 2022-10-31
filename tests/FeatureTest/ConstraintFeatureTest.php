<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\FeatureTest;

use Syndesi\CypherDataStructures\Type\Constraint;
use Syndesi\CypherDataStructures\Type\ConstraintName;
use Syndesi\CypherDataStructures\Type\ConstraintType;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Tests\FeatureTestCase;
use Syndesi\CypherEntityManager\Type\EntityManager;

class ConstraintFeatureTest extends FeatureTestCase
{
    public function testConstraint(): void
    {
        $nodeConstraintA = (new Constraint())
            ->setFor(new NodeLabel('NodeA'))
            ->setConstraintType(ConstraintType::UNIQUE)
            ->setConstraintName(new ConstraintName('constraint_node_a'))
            ->addProperty(new PropertyName('id'));
        $nodeConstraintB = (new Constraint())
            ->setFor(new NodeLabel('NodeB'))
            ->setConstraintType(ConstraintType::UNIQUE)
            ->setConstraintName(new ConstraintName('constraint_node_b'))
            ->addProperty(new PropertyName('id'))
            ->addProperty(new PropertyName('otherProperty'));
        $relationConstraint = (new Constraint())
            ->setFor(new RelationType('RELATION'))
            ->setConstraintType(ConstraintType::NOT_NULL)
            ->setConstraintName(new ConstraintName('constraint_relation'))
            ->addProperty(new PropertyName('id'));

        $em = $this->container->get(EntityManager::class);
        $this->assertConstraintDoesNotExist('constraint_node_a');
        $em->create($nodeConstraintA);
        $em->flush();
        $this->assertConstraintExist('constraint_node_a');
        $this->assertConstraintDoesNotExist('constraint_node_b');
        $em->create($nodeConstraintB);
        $em->flush();
        $this->assertConstraintExist('constraint_node_b');
        $this->assertConstraintDoesNotExist('constraint_relation');
        $em->create($relationConstraint);
        $em->flush();
        $this->assertConstraintExist('constraint_relation');
        $em->delete($nodeConstraintA);
        $em->flush();
        $this->assertConstraintDoesNotExist('constraint_node_a');
        $em->delete($nodeConstraintB);
        $em->flush();
        $this->assertConstraintDoesNotExist('constraint_node_b');
        $em->delete($relationConstraint);
        $em->flush();
        $this->assertConstraintDoesNotExist('constraint_relation');
    }
}
