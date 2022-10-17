<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherEntityManager\Contract\Event\PostFlushEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class PostFlushEvent implements PostFlushEventInterface
{
    use StoppableEventTrait;
}
