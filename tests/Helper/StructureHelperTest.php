<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Helper\StructureHelper;

class StructureHelperTest extends TestCase
{
    public function testEmptyStatement(): void
    {
        $statement = StructureHelper::getEmptyStatement();
        $this->assertSame('MATCH (n) LIMIT 0', $statement->getText());
    }

    public function testIdentifiersToStructure(): void
    {
        $node = (new Node())
            ->addProperty('id', 1000)
            ->addProperty('_id', 1001)
            ->addProperty('_z', 1002)
            ->addIdentifier('id')
            ->addIdentifier('_id')
            ->addIdentifier('_z');
        $this->assertSame(
            '_id, _z, id',
            StructureHelper::identifiersToStructure($node->getIdentifiers())
        );
    }

    public function testGetNodeStructure(): void
    {
        $node = (new Node())
            ->addLabel('Node')
            ->addProperty('id', 1234)
            ->addProperty('someKey', 'some value')
            ->addIdentifier('id');
        $this->assertSame('(:Node id)', StructureHelper::getNodeStructure($node));
    }

    public function testInvalidGetNodeStructure(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $node = (new Node())
            ->addLabel('Node')
            ->addProperty('someKey', 'some value');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one identifier is required');
        StructureHelper::getNodeStructure($node);
    }

    public function testGetRelationStructure(): void
    {
        $startNode = (new Node())
            ->addLabel('StartNode')
            ->addProperty('id', 1234)
            ->addIdentifier('id');

        $endNode = (new Node())
            ->addLabel('EndNode')
            ->addProperty('id', 4321)
            ->addIdentifier('id');

        $relation = (new Relation())
            ->setType('RELATION')
            ->setStartNode($startNode)
            ->setEndNode($endNode)
            ->addProperty('id', 1000)
            ->addIdentifier('id');

        $this->assertSame('(:StartNode id)-[RELATION id]->(:EndNode id)', StructureHelper::getRelationStructure($relation));
    }

    public function testInvalidGetRelationStructureWithMissingStartNode(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $endNode = (new Node())
            ->addLabel('EndNode')
            ->addProperty('id', 4321)
            ->addIdentifier('id');

        $relation = (new Relation())
            ->setType('RELATION')
            ->setEndNode($endNode)
            ->addProperty('id', 1000)
            ->addIdentifier('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Start node of relation can not be null');
        StructureHelper::getRelationStructure($relation);
    }

    public function testInvalidGetRelationStructureWithMissingEndNode(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $startNode = (new Node())
            ->addLabel('StartNode')
            ->addProperty('id', 1234)
            ->addIdentifier('id');

        $relation = (new Relation())
            ->setType('RELATION')
            ->setStartNode($startNode)
            ->addProperty('id', 1000)
            ->addIdentifier('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End node of relation can not be null');
        StructureHelper::getRelationStructure($relation);
    }

    public function testInvalidGetRelationStructureWithMissingIdentifier(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $startNode = (new Node())
            ->addLabel('StartNode')
            ->addProperty('id', 1234)
            ->addIdentifier('id');

        $endNode = (new Node())
            ->addLabel('EndNode')
            ->addProperty('id', 4321)
            ->addIdentifier('id');

        $relation = (new Relation())
            ->setType('RELATION')
            ->setStartNode($startNode)
            ->setEndNode($endNode)
            ->addProperty('id', 1000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one relation identifier is required');
        StructureHelper::getRelationStructure($relation);
    }
}
