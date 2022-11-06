<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Helper\StructureHelper;

class StructureHelperTest extends TestCase
{
    public function testIdentifierStorageToString(): void
    {
        $node = (new Node())
            ->addProperty(new PropertyName('id'), 1000)
            ->addProperty(new PropertyName('_id'), 1001)
            ->addProperty(new PropertyName('_z'), 1002)
            ->addIdentifier(new PropertyName('id'))
            ->addIdentifier(new PropertyName('_id'))
            ->addIdentifier(new PropertyName('_z'));
        $this->assertSame(
            '_id, _z, id',
            StructureHelper::identifierStorageToString($node->getIdentifiers())
        );
    }

    public function testGetNodeStructure(): void
    {
        $node = (new Node())
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 1234)
            ->addProperty(new PropertyName('someKey'), 'some value')
            ->addIdentifier(new PropertyName('id'));
        $this->assertSame('(:Node id)', StructureHelper::getNodeStructure($node));
    }

    public function testInvalidGetNodeStructure(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $node = (new Node())
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('someKey'), 'some value');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one identifier is required');
        StructureHelper::getNodeStructure($node);
    }

    public function testGetRelationStructure(): void
    {
        $startNode = (new Node())
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 1234)
            ->addIdentifier(new PropertyName('id'));

        $endNode = (new Node())
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 4321)
            ->addIdentifier(new PropertyName('id'));

        $relation = (new Relation())
            ->setRelationType(new RelationType('RELATION'))
            ->setStartNode($startNode)
            ->setEndNode($endNode)
            ->addProperty(new PropertyName('id'), 1000)
            ->addIdentifier(new PropertyName('id'));

        $this->assertSame('(:StartNode id)-[RELATION id]->(:EndNode id)', StructureHelper::getRelationStructure($relation));
    }

    public function testInvalidGetRelationStructureWithMissingStartNode(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $endNode = (new Node())
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 4321)
            ->addIdentifier(new PropertyName('id'));

        $relation = (new Relation())
            ->setRelationType(new RelationType('RELATION'))
            ->setEndNode($endNode)
            ->addProperty(new PropertyName('id'), 1000)
            ->addIdentifier(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('start node can not be null');
        StructureHelper::getRelationStructure($relation);
    }

    public function testInvalidGetRelationStructureWithMissingEndNode(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $startNode = (new Node())
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 1234)
            ->addIdentifier(new PropertyName('id'));

        $relation = (new Relation())
            ->setRelationType(new RelationType('RELATION'))
            ->setStartNode($startNode)
            ->addProperty(new PropertyName('id'), 1000)
            ->addIdentifier(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('end node can not be null');
        StructureHelper::getRelationStructure($relation);
    }

    public function testInvalidGetRelationStructureWithMissingIdentifier(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $startNode = (new Node())
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 1234)
            ->addIdentifier(new PropertyName('id'));

        $endNode = (new Node())
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 4321)
            ->addIdentifier(new PropertyName('id'));

        $relation = (new Relation())
            ->setRelationType(new RelationType('RELATION'))
            ->setStartNode($startNode)
            ->setEndNode($endNode)
            ->addProperty(new PropertyName('id'), 1000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one relation identifier is required');
        StructureHelper::getRelationStructure($relation);
    }
}
