<?php

namespace Halapi\Tests\Relation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Halapi\ObjectManager\DoctrineOrmObjectManager;
use Halapi\Relation\LinksRelation;
use Halapi\Relation\RelationInterface;
use Halapi\Tests\Fixtures\Entity\BlueCar;
use Halapi\Tests\Fixtures\Entity\Door;
use Halapi\Tests\Fixtures\Entity\Engine;
use PHPUnit\Framework\TestCase;
use Halapi\UrlGenerator\UrlGeneratorInterface;
use Halapi\AnnotationReader\AnnotationReaderInterface;

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
     * Set up mocks.
     */
    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->annotationReader = $this->createMock(AnnotationReaderInterface::class);
        $this->objectManager = $this->createMock(DoctrineOrmObjectManager::class);
    }

    /**
     * tests that the relation has the proper interface.
     */
    public function testInterface()
    {
        $linkRelation = new LinksRelation(
            $this->annotationReader,
            $this->urlGenerator,
            $this->objectManager
        );

        $this->assertInstanceOf(RelationInterface::class, $linkRelation);
    }

    /**
     * This relation should have the name _links.
     */
    public function testGetName()
    {
        $linkRelation = new LinksRelation(
            $this->annotationReader,
            $this->urlGenerator,
            $this->objectManager
        );

        $this->assertEquals('_links', $linkRelation->getName());
    }

    /**
     * Blue car has 2 doors and a V8 engine.
     */
    public function testGetRelation()
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $classMetadataMock->method('getIdentifier')->willReturn(['id']);
        $classMetadataMock->method('hasAssociation')->willReturn(true);
        $classMetadataMock->method('getAssociationTargetClass')->willReturnCallback(function ($property) {
            switch ($property) {
                case 'doors':
                    return Door::class;
                case 'engine':
                    return Engine::class;
            }
        });

        $this->objectManager
            ->method('getClassMetadata')
            ->willReturn($classMetadataMock);
        $this->objectManager
            ->method('getIdentifierName')
            ->willReturn('id');
        $this->objectManager
            ->method('getIdentifier')
            ->willReturnCallback(function ($entity) {
                return $entity->getId();
            })
        ;

        $this->urlGenerator
            ->method('generate')
            ->willReturnCallback(function ($routeName, $parameters) {
                $route = explode('_', $routeName);

                return '/'.$route[1].'s/'.$parameters[$route[1]];
            })
        ;

        // Are the properties of a bluecar embedable ?
        $blueCarReflectionClass = new \ReflectionClass(new BlueCar());
        $engineReflectionClass = new \ReflectionClass(new Engine());
        $this->annotationReader
            ->method('getAssociationRouteName')
            ->willReturnCallback(function ($property) use ($blueCarReflectionClass) {
                switch ($property) {
                    case $blueCarReflectionClass->getProperty('doors'):
                        return 'customget_door';
                    case $blueCarReflectionClass->getProperty('engine'):
                        return 'get_engine';
                }

                return;
            })
        ;
        $this->annotationReader
            ->method('getResourceRouteName')
            ->willReturnCallback(function ($resource) use ($blueCarReflectionClass, $engineReflectionClass) {
                switch ($resource) {
                    case $blueCarReflectionClass:
                        return 'customget_bluecar';
                    case $engineReflectionClass:
                        return 'get_engine';
                }

                return;
            })
        ;

        $this->annotationReader
            ->method('isEmbeddable')
            ->willReturnCallback(function ($property) {
                switch ($property->getName()) {
                    case 'id':
                        return false;
                }

                return true;
            })
        ;
        $linkRelation = new LinksRelation(
            $this->annotationReader,
            $this->urlGenerator,
            $this->objectManager
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
     * A collection should not have any links.
     */
    public function testArrayHasNoLinks()
    {
        $linkRelation = new LinksRelation(
            $this->annotationReader,
            $this->urlGenerator,
            $this->objectManager
        );

        $this->assertNull($linkRelation->getRelation(new ArrayCollection(['test'])));
    }
}
