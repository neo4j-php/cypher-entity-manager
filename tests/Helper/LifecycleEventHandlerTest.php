<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Constraint;
use Syndesi\CypherDataStructures\Type\Index;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\ConstraintPostCreateEvent;
use Syndesi\CypherEntityManager\Event\ConstraintPostDeleteEvent;
use Syndesi\CypherEntityManager\Event\ConstraintPreCreateEvent;
use Syndesi\CypherEntityManager\Event\ConstraintPreDeleteEvent;
use Syndesi\CypherEntityManager\Event\IndexPostCreateEvent;
use Syndesi\CypherEntityManager\Event\IndexPostDeleteEvent;
use Syndesi\CypherEntityManager\Event\IndexPreCreateEvent;
use Syndesi\CypherEntityManager\Event\IndexPreDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodePostCreateEvent;
use Syndesi\CypherEntityManager\Event\NodePostDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodePostMergeEvent;
use Syndesi\CypherEntityManager\Event\NodePreCreateEvent;
use Syndesi\CypherEntityManager\Event\NodePreDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodePreMergeEvent;
use Syndesi\CypherEntityManager\Event\RelationPostCreateEvent;
use Syndesi\CypherEntityManager\Event\RelationPostDeleteEvent;
use Syndesi\CypherEntityManager\Event\RelationPostMergeEvent;
use Syndesi\CypherEntityManager\Event\RelationPreCreateEvent;
use Syndesi\CypherEntityManager\Event\RelationPreDeleteEvent;
use Syndesi\CypherEntityManager\Event\RelationPreMergeEvent;
use Syndesi\CypherEntityManager\Helper\LifecycleEventHelper;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class LifecycleEventHandlerTest extends TestCase
{
    public function provideTestCases()
    {
        return [
            [
                new ActionCypherElement(ActionType::CREATE, new Node()),
                true,
                [
                    NodePreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Node()),
                false,
                [
                    NodePostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::MERGE, new Node()),
                true,
                [
                    NodePreMergeEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::MERGE, new Node()),
                false,
                [
                    NodePostMergeEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Node()),
                true,
                [
                    NodePreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Node()),
                false,
                [
                    NodePostDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Relation()),
                true,
                [
                    RelationPreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Relation()),
                false,
                [
                    RelationPostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::MERGE, new Relation()),
                true,
                [
                    RelationPreMergeEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::MERGE, new Relation()),
                false,
                [
                    RelationPostMergeEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Relation()),
                true,
                [
                    RelationPreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Relation()),
                false,
                [
                    RelationPostDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Index()),
                true,
                [
                    IndexPreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Index()),
                false,
                [
                    IndexPostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Index()),
                true,
                [
                    IndexPreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Index()),
                false,
                [
                    IndexPostDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Constraint()),
                true,
                [
                    ConstraintPreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new Constraint()),
                false,
                [
                    ConstraintPostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Constraint()),
                true,
                [
                    ConstraintPreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new Constraint()),
                false,
                [
                    ConstraintPostDeleteEvent::class,
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideTestCases
     */
    public function testCases(ActionCypherElement $actionCypherElement, bool $isPre, array $expectedEvents): void
    {
        $actualEvents = LifecycleEventHelper::getLifecycleEventForCypherActionElement($actionCypherElement, $isPre);
        $this->assertSame(count($expectedEvents), count($actualEvents));
        foreach ($expectedEvents as $i => $expectedEvent) {
            $this->assertInstanceOf($expectedEvent, $actualEvents[$i]);
        }
    }
}