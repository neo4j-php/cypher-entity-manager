<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Helper\Statement;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherEntityManager\Helper\Statement\CreateNodeStatement;

class CreateNodeStatementTest extends TestCase
{
    public function testNodeStatement(): void
    {
        $node = new Node();
        $node
            ->addNodeLabel(new NodeLabel("NodeLabel"))
            ->addProperty(new PropertyName('id'), 1234)
            ->addProperty(new PropertyName('some'), 'value')
            ->addIdentifier(new PropertyName('id'));
        $statement = CreateNodeStatement::nodeStatement($node);

        $this->assertSame('CREATE (:NodeLabel {id: $id, some: $some})', $statement->getText());
        $this->assertCount(2, $statement->getParameters());
        $this->assertSame(1234, $statement->getParameters()['id']);
        $this->assertSame('value', $statement->getParameters()['some']);
    }
}
