<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeInterface;

interface NodePostMergeEventInterface extends PostMergeEventInterface
{
    public function getElement(): NodeInterface;
}
