<?php

namespace Halapi\Tests\Relation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Halapi\Annotation\Embeddable;
use Halapi\Relation\LinksRelation;
use Halapi\Relation\RelationInterface;
use Doctrine\Common\Annotations\Reader;
use Halapi\Tests\Fixtures\Entity\BlueCar;
use Halapi\Tests\Fixtures\Entity\Door;
use Halapi\Tests\Fixtures\Entity\Engine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class LinksRelationTest.
 *
 * @author Romain Richard
 */
class LinksRelationTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $annotationReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestStack;

    /**
     * Set up mocks
     */
    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->annotationReader = $this->createMock(Reader::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->requestStack = $this->createMock(RequestStack::class);
    }

    /**
     * tests that the relation has the proper interface.
     */
    public function testInterface()
    {
        $linkRelation = new LinksRelation(
            $this->urlGenerator,
            $this->annotationReader,
            $this->objectManager,
            $this->requestStack
        );

        $this->assertInstanceOf(RelationInterface::class, $linkRelation);
    }

    /**
     * This relation should have the name _links
     */
    public function testGetName()
    {
        $linkRelation = new LinksRelation(
            $this->urlGenerator,
            $this->annotationReader,
            $this->objectManager,
            $this->requestStack
        );

        $this->assertEquals('_links', $linkRelation->getName());
    }

    /**
     * Blue car has 2 doors.
     */
    public function testGetRelation()
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $classMetadataMock->method('getIdentifier')->willReturn(['id']);
        $this->objectManager
            ->method('getClassMetadata')
            ->willReturn($classMetadataMock);

        $this->urlGenerator
            ->method('generate')
            ->willReturnCallback(function ($routeName, $parameters) {
                $route = explode('_', $routeName);

                return '/'.$route[1].'s/'.$parameters[$route[1]];
            })
        ;

        // Are the properties of a bluecar embedable ?
        $reflectionClass = new \ReflectionClass(new BlueCar());
        $this->annotationReader
            ->method('getPropertyAnnotation')
            ->willReturnCallback(function ($property, $class) use ($reflectionClass) {
                if (Embeddable::class === $class) {
                    switch ($property) {
                        case $reflectionClass->getProperty('doors'):
                            return true;
                        case $reflectionClass->getProperty('engine'):
                            return true;
                    }
                }

                return null;
            })
        ;

        $this->annotationReader
            ->method('getPropertyAnnotations')
            ->willReturnCallback(function ($property) use ($reflectionClass) {
                switch ($property) {
                    case $reflectionClass->getProperty('doors'):
                        $doorsRelationAnnotation = new OneToMany();
                        $doorsRelationAnnotation->targetEntity = Door::class;

                        return [new Annotation([]), $doorsRelationAnnotation];
                    case $reflectionClass->getProperty('engine'):
                        $engineRelationAnnotation = new ManyToOne();
                        $engineRelationAnnotation->targetEntity = Engine::class;

                        return [new Annotation([]), $engineRelationAnnotation];
                    default:
                        return [];
                }
            })
        ;

        $linkRelation = new LinksRelation(
            $this->urlGenerator,
            $this->annotationReader,
            $this->objectManager,
            $this->requestStack
        );

        $leftDoor = new Door();
        $leftDoor->setId(1);
        $leftDoor->setSide('left');

        $rightDoor = new Door();
        $rightDoor->setId(2);
        $rightDoor->setSide('right');

        $engine = new Engine();
        $engine->setId(23);
        $engine->setHorses(12);
        $engine->setName('V8');

        $blueCar = new BlueCar();
        $blueCar->setId(1);
        $blueCar->setDoors(new ArrayCollection([$leftDoor, $rightDoor]));
        $blueCar->setEngine($engine);

        $this->assertEquals(
            [
                'self' => '/bluecars/1',
                'doors' => [
                    '/doors/1',
                    '/doors/2',
                ],
                'engine' => '/engines/23',
            ],
            $linkRelation->getRelation($blueCar)
        );

        $blueCarWithoutEngine = new BlueCar();
        $blueCarWithoutEngine->setId(2);
        $blueCarWithoutEngine->setDoors(new ArrayCollection([$leftDoor, $rightDoor]));

        $this->assertEquals(
            [
                'self' => '/bluecars/2',
                'doors' => [
                    '/doors/1',
                    '/doors/2',
                ],
            ],
            $linkRelation->getRelation($blueCarWithoutEngine)
        );
    }

    /**
     * A collection should not have any links
     */
    public function testArrayHasNoLinks()
    {
        $linkRelation = new LinksRelation(
            $this->urlGenerator,
            $this->annotationReader,
            $this->objectManager,
            $this->requestStack
        );

        $this->assertNull($linkRelation->getRelation(new ArrayCollection(['test'])));
    }
}
