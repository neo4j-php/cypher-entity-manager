<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherEntityManager\Contract\Event\PreFlushEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class PreFlushEvent implements PreFlushEventInterface
{
    use StoppableEventTrait;
}
