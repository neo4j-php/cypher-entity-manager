<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper;

use LogicException;
use Syndesi\CypherEntityManager\Contract\Event\LifecycleEventInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
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
            ActionCypherElementType::INDEX->name => [
                'Pre' => [
                    ActionType::CREATE->name => IndexPreCreateEvent::class,
                    ActionType::DELETE->name => IndexPreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => IndexPostCreateEvent::class,
                    ActionType::DELETE->name => IndexPostDeleteEvent::class,
                ],
            ],
            ActionCypherElementType::CONSTRAINT->name => [
                'Pre' => [
                    ActionType::CREATE->name => ConstraintPreCreateEvent::class,
                    ActionType::DELETE->name => ConstraintPreDeleteEvent::class,
                ],
                'Post' => [
                    ActionType::CREATE->name => ConstraintPostCreateEvent::class,
                    ActionType::DELETE->name => ConstraintPostDeleteEvent::class,
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
        throw new LogicException("this line can not be reached");
    }
}
