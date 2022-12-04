<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper;

use Syndesi\CypherEntityManager\Contract\Event\LifecycleEventInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
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
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionCypherElementType;
use Syndesi\CypherEntityManager\Type\ActionType;

class LifecycleEventHelper
{
    /**
     * @return LifecycleEventInterface[]
     */
    public static function getLifecycleEventForCypherActionElement(ActionCypherElement $actionCypherElement, bool $isPre): array
    {
        $eventClasses = [
            ActionCypherElementType::NODE->name => [
                'Pre' => [
                    ActionType::CREATE->name => NodePreCreateEvent::class,
                    ActionType::MERGE->name => NodePreMergeEvent::class,
                    ActionType::DELETE->name => NodePreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => NodePostCreateEvent::class,
                    ActionType::MERGE->name => NodePostMergeEvent::class,
                    ActionType::DELETE->name => NodePostDeleteEvent::class,
                ],
            ],
            ActionCypherElementType::RELATION->name => [
                'Pre' => [
                    ActionType::CREATE->name => RelationPreCreateEvent::class,
                    ActionType::MERGE->name => RelationPreMergeEvent::class,
                    ActionType::DELETE->name => RelationPreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => RelationPostCreateEvent::class,
                    ActionType::MERGE->name => RelationPostMergeEvent::class,
                    ActionType::DELETE->name => RelationPostDeleteEvent::class,
                ],
            ],
            ActionCypherElementType::NODE_INDEX->name => [
                'Pre' => [
                    ActionType::CREATE->name => NodeIndexPreCreateEvent::class,
                    ActionType::DELETE->name => NodeIndexPreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => NodeIndexPostCreateEvent::class,
                    ActionType::DELETE->name => NodeIndexPostDeleteEvent::class,
                ],
            ],
            ActionCypherElementType::RELATION_INDEX->name => [
                'Pre' => [
                    ActionType::CREATE->name => RelationIndexPreCreateEvent::class,
                    ActionType::DELETE->name => RelationIndexPreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => RelationIndexPostCreateEvent::class,
                    ActionType::DELETE->name => RelationIndexPostDeleteEvent::class,
                ],
            ],
            ActionCypherElementType::NODE_CONSTRAINT->name => [
                'Pre' => [
                    ActionType::CREATE->name => NodeConstraintPreCreateEvent::class,
                    ActionType::DELETE->name => NodeConstraintPreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => NodeConstraintPostCreateEvent::class,
                    ActionType::DELETE->name => NodeConstraintPostDeleteEvent::class,
                ],
            ],
            ActionCypherElementType::RELATION_CONSTRAINT->name => [
                'Pre' => [
                    ActionType::CREATE->name => RelationConstraintPreCreateEvent::class,
                    ActionType::DELETE->name => RelationConstraintPreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => RelationConstraintPostCreateEvent::class,
                    ActionType::DELETE->name => RelationConstraintPostDeleteEvent::class,
                ],
            ],
        ];
        $elementType = ActionCypherElementHelper::getTypeFromActionCypherElement($actionCypherElement);
        if (array_key_exists($elementType->name, $eventClasses)) {
            $eventClass = $eventClasses[$elementType->name];
            if (array_key_exists($isPre ? 'Pre' : 'Post', $eventClass)) {
                $eventClass = $eventClass[$isPre ? 'Pre' : 'Post'];
                if (array_key_exists($actionCypherElement->getAction()->name, $eventClass)) {
                    $eventClass = $eventClass[$actionCypherElement->getAction()->name];

                    return [
                        /**
                         * @phpstan-ignore-next-line
                         */
                        new $eventClass($actionCypherElement->getElement()),
                    ];
                }
            }
        }
        $element = $actionCypherElement->getElement();
        if ($element instanceof SimilarNodeQueueInterface) {
            $events = [];
            // todo build events
            return $events;
        }
        if ($element instanceof SimilarRelationQueueInterface) {
            $events = [];
            // todo build events
            return $events;
        }
        throw new \LogicException("this line can not be reached");
    }
}
