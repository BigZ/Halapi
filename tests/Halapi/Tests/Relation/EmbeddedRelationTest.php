<?php

namespace Halapi\Tests\Relation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\OneToMany;
use Halapi\Annotation\Embeddable;
use Halapi\Relation\EmbeddedRelation;
use Halapi\Relation\RelationInterface;
use Halapi\AnnotationReader\AnnotationReaderInterface;
use Halapi\Tests\Fixtures\Entity\BlueCar;
use Halapi\Tests\Fixtures\Entity\Door;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class EmbeddedRelationTest.
 *
 * @author Romain Richard
 */
class EmbeddedRelationTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $annotationReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * Set up mocks.
     */
    public function setUp()
    {
        $this->annotationReader = $this->createMock(AnnotationReaderInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    /**
     * tests that the relation has the proper interface.
     */
    public function testInterface()
    {
        $embeddedRelation = new EmbeddedRelation(
            $this->annotationReader,
            $this->request
        );

        $this->assertInstanceOf(RelationInterface::class, $embeddedRelation);
    }

    /**
     * Name should be _embedded.
     */
    public function testGetName()
    {
        $embeddedRelation = new EmbeddedRelation(
            $this->annotationReader,
            $this->request
        );

        $this->assertEquals('_embedded', $embeddedRelation->getName());
    }

    /**
     * Blue car has 2 doors.
     */
    public function testGetRelation()
    {
        $this->request->expects($this->at(0))->method('getQueryParams')->willReturn(['embed' => ['doors']]);
        $this->request->expects($this->at(1))->method('getQueryParams')->willReturn(['embed' => 'wrong']);

        // Are the properties of a bluecar embedable ?
        $reflectionClass = new \ReflectionClass(new BlueCar());
        $this->annotationReader
            ->expects($this->at(0))
            ->method('isEmbeddable')
            ->with(
                $reflectionClass->getProperty('id')
            )
            ->willReturn(false)
        ;
        $this->annotationReader
            ->expects($this->at(1))
            ->method('isEmbeddable')
            ->with(
                $reflectionClass->getProperty('doors')
            )
            ->willReturn(true)
        ;

        $doorsRelationAnnotation = new OneToMany();
        $doorsRelationAnnotation->targetEntity = Door::class;

        $embeddedRelation = new EmbeddedRelation(
            $this->annotationReader,
            $this->request
        );

        $leftDoor = new Door();
        $leftDoor->setId(1);
        $leftDoor->setSide('left');

        $rightDoor = new Door();
        $rightDoor->setId(2);
        $rightDoor->setSide('right');

        $blueCar = new BlueCar();
        $blueCar->setId(1);
        $blueCar->setDoors(new ArrayCollection([$leftDoor, $rightDoor]));

        $this->assertEquals(
            [
                'doors' => [
                    ['id' => 1, 'side' => 'left'],
                    ['id' => 2, 'side' => 'right'],
                ],
            ],
            $embeddedRelation->getRelation($blueCar)
        );
        $this->assertEquals([], $embeddedRelation->getRelation($blueCar));
    }
}
