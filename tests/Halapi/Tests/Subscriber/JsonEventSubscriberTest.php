<?php

namespace Halapi\Subscriber;

use Halapi\Factory\RelationFactory;
use Halapi\Representation\PaginatedRepresentation;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use PHPUnit\Framework\TestCase;
use JMS\Serializer\EventDispatcher\Events;

/**
 * Class JsonEventSubscriberTest.
 *
 * @author Romain Richard
 */
class JsonEventSubscriberTest extends TestCase
{
    /**
     * Test that we subscribe to the proper event.
     */
    public function testSubscribedEvents()
    {
        in_array([
            'event' => Events::POST_SERIALIZE,
            'format' => 'json',
            'method' => 'onPostSerialize',
        ], JsonEventSubscriber::getSubscribedEvents());
    }

    /**
     * Test that a PaginatedRepresentation does not get extra fields on serialization.
     */
    public function testDontSerialiazePaginatedRepresentation()
    {
        $eventPaginatedMock = $this->createMock(ObjectEvent::class);
        $eventPaginatedMock
            ->method('getObject')
            ->willReturn(new PaginatedRepresentation(1, 1, [], []))
        ;

        $relationFactoryMock = $this->createMock(RelationFactory::class);
        $jsonEventSubscriber = new JsonEventSubscriber($relationFactoryMock);
        $this->assertNull($jsonEventSubscriber->onPostSerialize($eventPaginatedMock));
    }

    /**
     * Test that relation data are added to serialized objects.
     */
    public function testAddRelationDataOnSerialization()
    {
        $relationFactoryMock = $this->createMock(RelationFactory::class);
        $relationFactoryMock->method('getRelations')->willReturn(['_links' => ['/tests/1'], '_embedded' => ['test']]);

        $visitorMock = $this->createMock(GenericSerializationVisitor::class);
        $visitorMock->expects($this->at(0))->method('addData')->with('_links', ['/tests/1']);
        $visitorMock->expects($this->at(1))->method('addData')->with('_embedded', ['test']);

        $eventMock = $this->createMock(ObjectEvent::class);
        $eventMock
            ->method('getObject')
            ->willReturn('')
        ;
        $eventMock->method('getVisitor')->willReturn($visitorMock);

        $jsonEventSubscriber = new JsonEventSubscriber($relationFactoryMock);
        $jsonEventSubscriber->onPostSerialize($eventMock);
    }
}
