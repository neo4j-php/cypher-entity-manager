<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Psr\EventDispatcher\StoppableEventInterface;

interface EventInterface extends StoppableEventInterface
{
    public function stopPropagation(): void;
}
