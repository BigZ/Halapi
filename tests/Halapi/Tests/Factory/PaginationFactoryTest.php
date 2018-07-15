<?php

namespace Halapi\Tests\Factory;

use Doctrine\ORM\Query;
use Halapi\AnnotationReader\AnnotationReaderInterface;
use Halapi\ObjectManager\ObjectManagerInterface;
use Halapi\Pager\PagerInterface;
use Halapi\Tests\Fixtures\Entity\BlueCar;
use Halapi\UrlGenerator\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Halapi\Factory\PaginationFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PaginationFactoryTest
 * @author Romain Richard
 */
class PaginationFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ServerRequestInterface
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PagerInterface
     */
    private $pager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * Set up mocks.
     */
    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->pager = $this->createMock(PagerInterface::class);
        $this->annotationReader = $this->createMock(AnnotationReaderInterface::class);
    }

    /**
     * Get the paginated representation of an entity.
     */
    public function testGetRepresentation()
    {
        $this->request->method('getQueryParams')->willReturn(['page' => '1', 'limit' => '2']);

        $blueCar1 = new BlueCar(1);
        $blueCar2 = new BlueCar(2);
        $blueCar3 = new BlueCar(3);
        $blueCar4 = new BlueCar(4);
        $collection = [$blueCar1, $blueCar2, $blueCar3, $blueCar4];

        $this->objectManager->method('findAllSorted')->willReturn(42);

        $this->pager->method('getPageCount')->willReturn(2);
        $this->pager->method('getCurrentPageResults')->willReturn(array_slice($collection, 0, 2));

        $this->urlGenerator->method('generate')->willReturnCallback(function ($name, $params) {
            return $name.'?page='.$params['page'].'&limit='.$params['limit'];
        });

        $this->annotationReader->method('getResourceCollectionRouteName')->willReturn('get_bluecars');

        $paginationFactory = new PaginationFactory(
            $this->urlGenerator,
            $this->objectManager,
            $this->request,
            $this->pager,
            $this->annotationReader
        );
        $representation = $paginationFactory->getRepresentation(BlueCar::class);

        $this->assertEquals(1, $representation->page);
        $this->assertEquals(2, $representation->limit);
        $this->assertEquals([
            'self' => 'get_bluecars?page=1&limit=2',
            'first' => 'get_bluecars?page=1&limit=2',
            'next' => 'get_bluecars?page=2&limit=2',
            'last' => 'get_bluecars?page=2&limit=2',
        ], $representation->_links);
        $this->assertEquals(array_slice($collection, 0, 2), $representation->_embedded);
    }
}
