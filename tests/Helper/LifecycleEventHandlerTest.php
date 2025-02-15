<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeConstraint;
use Syndesi\CypherDataStructures\Type\NodeIndex;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationConstraint;
use Syndesi\CypherDataStructures\Type\RelationIndex;
use Syndesi\CypherEntityManager\Event\NodeConstraintPostCreateEvent;
use Syndesi\CypherEntityManager\Event\NodeConstraintPostDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodeConstraintPreCreateEvent;
use Syndesi\CypherEntityManager\Event\NodeConstraintPreDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodeIndexPostCreateEvent;
use Syndesi\CypherEntityManager\Event\NodeIndexPostDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodeIndexPreCreateEvent;
use Syndesi\CypherEntityManager\Event\NodeIndexPreDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodePostCreateEvent;
use Syndesi\CypherEntityManager\Event\NodePostDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodePostMergeEvent;
use Syndesi\CypherEntityManager\Event\NodePreCreateEvent;
use Syndesi\CypherEntityManager\Event\NodePreDeleteEvent;
use Syndesi\CypherEntityManager\Event\NodePreMergeEvent;
use Syndesi\CypherEntityManager\Event\RelationConstraintPostCreateEvent;
use Syndesi\CypherEntityManager\Event\RelationConstraintPostDeleteEvent;
use Syndesi\CypherEntityManager\Event\RelationConstraintPreCreateEvent;
use Syndesi\CypherEntityManager\Event\RelationConstraintPreDeleteEvent;
use Syndesi\CypherEntityManager\Event\RelationIndexPostCreateEvent;
use Syndesi\CypherEntityManager\Event\RelationIndexPostDeleteEvent;
use Syndesi\CypherEntityManager\Event\RelationIndexPreCreateEvent;
use Syndesi\CypherEntityManager\Event\RelationIndexPreDeleteEvent;
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
    public static function provideTestCases()
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
                new ActionCypherElement(ActionType::CREATE, new NodeIndex()),
                true,
                [
                    NodeIndexPreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new NodeIndex()),
                false,
                [
                    NodeIndexPostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new NodeIndex()),
                true,
                [
                    NodeIndexPreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new NodeIndex()),
                false,
                [
                    NodeIndexPostDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new RelationIndex()),
                true,
                [
                    RelationIndexPreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new RelationIndex()),
                false,
                [
                    RelationIndexPostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new RelationIndex()),
                true,
                [
                    RelationIndexPreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new RelationIndex()),
                false,
                [
                    RelationIndexPostDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new NodeConstraint()),
                true,
                [
                    NodeConstraintPreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new NodeConstraint()),
                false,
                [
                    NodeConstraintPostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new NodeConstraint()),
                true,
                [
                    NodeConstraintPreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new NodeConstraint()),
                false,
                [
                    NodeConstraintPostDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new RelationConstraint()),
                true,
                [
                    RelationConstraintPreCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::CREATE, new RelationConstraint()),
                false,
                [
                    RelationConstraintPostCreateEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new RelationConstraint()),
                true,
                [
                    RelationConstraintPreDeleteEvent::class,
                ],
            ],
            [
                new ActionCypherElement(ActionType::DELETE, new RelationConstraint()),
                false,
                [
                    RelationConstraintPostDeleteEvent::class,
                ],
            ],
        ];
    }

    #[DataProvider("provideTestCases")]
    public function testCases(ActionCypherElement $actionCypherElement, bool $isPre, array $expectedEvents): void
    {
        $actualEvents = LifecycleEventHelper::getLifecycleEventForCypherActionElement($actionCypherElement, $isPre);
        $this->assertSame(count($expectedEvents), count($actualEvents));
        foreach ($expectedEvents as $i => $expectedEvent) {
            $this->assertInstanceOf($expectedEvent, $actualEvents[$i]);
        }
    }
}
