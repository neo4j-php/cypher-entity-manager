<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

enum ActionCypherElementType: string
{
    case NODE = 'NODE';
    case RELATION = 'RELATION';
    case INDEX = 'INDEX';
    case CONSTRAINT = 'CONSTRAINT';
    case SIMILAR_NODE_QUEUE = 'SIMILAR_NODE_QUEUE';
    case SIMILAR_RELATION_QUEUE = 'SIMILAR_RELATION_QUEUE';
}
