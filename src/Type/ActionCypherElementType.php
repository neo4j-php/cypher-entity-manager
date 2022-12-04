<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

enum ActionCypherElementType: string
{
    case NODE = 'NODE';
    case RELATION = 'RELATION';
    case NODE_INDEX = 'NODE_INDEX';
    case RELATION_INDEX = 'RELATION_INDEX';
    case NODE_CONSTRAINT = 'NODE_CONSTRAINT';
    case RELATION_CONSTRAINT = 'RELATION_CONSTRAINT';
    case SIMILAR_NODE_QUEUE = 'SIMILAR_NODE_QUEUE';
    case SIMILAR_RELATION_QUEUE = 'SIMILAR_RELATION_QUEUE';
}
