<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;

interface ConstraintPrePersistEventInterface extends PrePersistEventInterface
{
    public function getElement(): ConstraintInterface;
}
