<?php

namespace Halapi\Tests\Relation;

use Halapi\Factory\RelationFactory;
use Halapi\HalapiBuilder;
use Halapi\Subscriber\JsonEventSubscriber;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class HalapiBuilderTest
 * @author Romain Richard
 */
class HalapiBuilderTest extends TestCase
{
    /**
     * Test that the proper listeners are added to the returned serializer.
     */
    public function testGetSerializer()
    {
        $relationFactoryMock = $this->createMock(RelationFactory::class);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcherMock
            ->expects($this->once())
            ->method('addSubscriber')
            ->with(new JsonEventSubscriber($relationFactoryMock));
       
        $serializerBuilderMock = $this->createMock(SerializerBuilder::class);
        $serializerBuilderMock->expects($this->once())->method('build')->willReturn(true);
        $serializerBuilderMock
            ->method('configureListeners')
            ->willReturnCallback(function ($callback) use ($eventDispatcherMock) {
                $callback($eventDispatcherMock);
        });
        
        $serializerBuilderMock->expects($this->once())->method('addDefaultListeners')->willReturn($serializerBuilderMock);
        
        $halapiBuilder = new HalapiBuilder($relationFactoryMock, $serializerBuilderMock);
        $this->assertTrue($halapiBuilder->getSerializer());
    }
}
