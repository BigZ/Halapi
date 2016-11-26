<?php

namespace Halapi\Tests\Relation;


use Halapi\Relation\LinksRelation;
use Halapi\Relation\RelationInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @covers LinksRelation
 */
class LinksRelationTest extends TestCase
{
    public function testInterface()
    {
        $router = $this->createMock(RouterInterface::class);
        $annotationReader = $this->createMock(Reader::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $object = new LinksRelation($router, $annotationReader, $entityManager, $requestStack);

        $this->assertInstanceOf(RelationInterface::class, $object);
    }

    public function testGetSelfLink()
    {
        $router = $this->createMock(RouterInterface::class);
        $annotationReader = $this->createMock(Reader::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $object = new LinksRelation($router, $annotationReader, $entityManager, $requestStack);

        $this->assertEquals([], $object);
    }
}

class LinkRelationTestObject
{
    private $id;

    private $relation;
}