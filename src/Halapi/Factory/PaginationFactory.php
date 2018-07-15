<?php

namespace Halapi\Factory;

use Halapi\ObjectManager\ObjectManagerInterface;
use Halapi\Pager\PagerInterface;
use Halapi\Representation\PaginatedRepresentation;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Halapi\UrlGenerator\UrlGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Halapi\AnnotationReader\AnnotationReaderInterface;

/**
 * Class PaginationFactory.
 *
 * @author Romain Richard
 */
class PaginationFactory
{
    /**
     * @var ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var UrlGeneratorInterface
     */
    public $urlGenerator;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var PagerInterface
     */
    private $pager;

    /**
     * @var AnnotationReaderInterface
     */
    protected $annotationReader;

    /**
     * PaginationFactory constructor.
     *
     * @param UrlGeneratorInterface     $urlGenerator
     * @param ObjectManagerInterface    $objectManager
     * @param ServerRequestInterface    $request
     * @param PagerInterface            $pager
     * @param AnnotationReaderInterface $annotationReader
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ObjectManagerInterface $objectManager,
        ServerRequestInterface $request,
        PagerInterface $pager,
        AnnotationReaderInterface $annotationReader
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->pager = $pager;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Get a paginated representation of a collection of entities.
     * Your repository for the object $className must implement the 'findAllSorted' method.
     *
     * @param string $className
     *
     * @return PaginatedRepresentation
     */
    public function getRepresentation($className)
    {
        list($page, $limit, $sorting, $filterValues, $filerOperators) = array_values($this->addPaginationParams());
        $results = $this->objectManager->findAllSorted($className, $sorting, $filterValues, $filerOperators);

        $this->pager->setResults($results);
        $this->pager->setMaxPerPage($limit);
        $this->pager->setCurrentPage($page);

        return new PaginatedRepresentation(
            $page,
            $limit,
            [
                'self' => $this->getPaginatedRoute($className, $limit, $page, $sorting),
                'first' => $this->getPaginatedRoute($className, $limit, 1, $sorting),
                'next' => $this->getPaginatedRoute(
                    $className,
                    $limit,
                    $page < $this->pager->getPageCount() ? $page + 1 : $this->pager->getPageCount(),
                    $sorting
                ),
                'last' => $this->getPaginatedRoute($className, $limit, $this->pager->getPageCount(), $sorting),
            ],
            (array) $this->pager->getCurrentPageResults()
        );
    }

    /**
     * Get the pagination parameters, filtered.
     *
     * @return array
     */
    private function addPaginationParams()
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults(array(
            'page' => '1',
            'limit' => '20',
            'sorting' => [],
            'filtervalue' => [],
            'filteroperator' => [],
        ));

        $resolver->setAllowedTypes('page', ['NULL', 'string']);
        $resolver->setAllowedTypes('limit', ['NULL', 'string']);
        $resolver->setAllowedTypes('sorting', ['NULL', 'array']);
        $resolver->setAllowedTypes('filtervalue', ['NULL', 'array']);
        $resolver->setAllowedTypes('filteroperator', ['NULL', 'array']);

        $queryParams = $this->request->getQueryParams();

        return $resolver->resolve(array_filter([
            'page' => isset($queryParams['page']) ? $queryParams['page'] : '',
            'limit' => isset($queryParams['limit']) ? $queryParams['limit'] : '',
            'sorting' => isset($queryParams['sorting']) ? $queryParams['sorting'] : '',
            'filtervalue' => isset($queryParams['filtervalue']) ? $queryParams['filtervalue'] : '',
            'filteroperator' => isset($queryParams['filteroperator']) ? $queryParams['filteroperator'] : '',
        ]));
    }

    /**
     * @param $name string
     * @param $limit int
     * @param $page int
     * @param $sorting string
     *
     * @return string|null
     *
     * @throws \ReflectionException
     */
    private function getPaginatedRoute($name, $limit, $page, $sorting)
    {
        return $this->urlGenerator->generate(
            $this->annotationReader->getResourceCollectionRouteName(new \ReflectionClass($name)),
            [
                'sorting' => $sorting,
                'page' => $page,
                'limit' => $limit,
            ]
        );
    }
}
