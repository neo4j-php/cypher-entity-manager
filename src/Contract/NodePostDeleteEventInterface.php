<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\NodeInterface;

interface NodePostDeleteEventInterface extends PostDeleteEventInterface
{
    public function getElement(): NodeInterface;
}
