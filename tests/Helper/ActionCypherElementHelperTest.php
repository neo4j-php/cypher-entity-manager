<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Constraint;
use Syndesi\CypherDataStructures\Type\Index;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Helper\ActionCypherElementHelper;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionCypherElementType;
use Syndesi\CypherEntityManager\Type\ActionType;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;
use Syndesi\CypherEntityManager\Type\SimilarRelationQueue;

class ActionCypherElementHelperTest extends TestCase
{
    public function provideActionCypherElementWithType()
    {
        return [
            [
                new ActionCypherElement(ActionType::CREATE, new Node()),
                ActionCypherElementType::NODE,
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Relation()),
                ActionCypherElementType::RELATION,
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Index()),
                ActionCypherElementType::INDEX,
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Constraint()),
                ActionCypherElementType::CONSTRAINT,
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new SimilarNodeQueue()),
                ActionCypherElementType::SIMILAR_NODE_QUEUE,
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new SimilarRelationQueue()),
                ActionCypherElementType::SIMILAR_RELATION_QUEUE,
            ],
        ];
    }

    /**
     * @dataProvider provideActionCypherElementWithType
     */
    public function testGetTypeFromActionCypherElement(ActionCypherElement $object, ActionCypherElementType $expectedType): void
    {
        $foundType = ActionCypherElementHelper::getTypeFromActionCypherElement($object);
        $this->assertSame($expectedType, $foundType);
    }
}
