<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;

interface ConstraintPostPersistEventInterface extends PostPersistEventInterface
{
    public function getElement(): ConstraintInterface;
}
