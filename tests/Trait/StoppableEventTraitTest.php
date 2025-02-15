<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherEntityManager\Contract\Event\EventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class StoppableEventTraitTest extends TestCase
{
    private function getTrait(): EventInterface
    {
        return new class implements EventInterface {
            use StoppableEventTrait;
        };
    }

    public function testStoppableEventTrait(): void
    {
        $trait = $this->getTrait();
        $this->assertFalse($trait->isPropagationStopped());
        $trait->stopPropagation();
        $this->assertTrue($trait->isPropagationStopped());
    }
}
