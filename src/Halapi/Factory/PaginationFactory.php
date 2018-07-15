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
        list($page, $limit, $sort, $filters, $filerOperators) = array_values($this->addPaginationParams());
        $sorting = $this->parseSorting($sort);
        $results = $this->objectManager->findAllSorted($className, $sorting, $filters, $filerOperators);

        $this->pager->setResults($results);
        $this->pager->setMaxPerPage($limit);
        $this->pager->setCurrentPage($page);

        return new PaginatedRepresentation(
            $page,
            $limit,
            [
                'self' => $this->getPaginatedRoute($className, $limit, $page, $sort),
                'first' => $this->getPaginatedRoute($className, $limit, 1, $sort),
                'next' => $this->getPaginatedRoute(
                    $className,
                    $limit,
                    $page < $this->pager->getPageCount() ? $page + 1 : $this->pager->getPageCount(),
                    $sort
                ),
                'last' => $this->getPaginatedRoute($className, $limit, $this->pager->getPageCount(), $sort),
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
            'sort' => null,
            'filter' => [],
            'filteroperator' => [],
        ));

        $resolver->setAllowedTypes('page', ['NULL', 'string']);
        $resolver->setAllowedTypes('limit', ['NULL', 'string']);
        $resolver->setAllowedTypes('sort', ['NULL', 'string']);
        $resolver->setAllowedTypes('filter', ['NULL', 'array']);
        $resolver->setAllowedTypes('filteroperator', ['NULL', 'array']);

        $queryParams = $this->request->getQueryParams();

        return $resolver->resolve(array_filter([
            'page' => isset($queryParams['page']) ? $queryParams['page'] : '',
            'limit' => isset($queryParams['limit']) ? $queryParams['limit'] : '',
            'sort' => isset($queryParams['sort']) ? $queryParams['sort'] : '',
            'filter' => isset($queryParams['filter']) ? $queryParams['filter'] : '',
            'filteroperator' => isset($queryParams['filteroperator']) ? $queryParams['filteroperator'] : '',
        ]));
    }

    /**
     * @param $name string
     * @param $limit int
     * @param $page int
     * @param $sort string
     *
     * @return string|null
     *
     * @throws \ReflectionException
     */
    private function getPaginatedRoute($name, $limit, $page, $sort)
    {
        return $this->urlGenerator->generate(
            $this->annotationReader->getResourceCollectionRouteName(new \ReflectionClass($name)),
            [
                'sort' => $sort,
                'page' => $page,
                'limit' => $limit,
            ]
        );
    }

    /**
     * Parse a jsonapi formatted sorting string to an array.
     * @param $sort
     * @return array
     */
    private function parseSorting($sort)
    {
        $parsed = [];

        if ($sort) {
            $nameList = explode(',', $sort);
            foreach ($nameList as $name) {
                if ('-' === $name[0]) {
                    $parsed[substr($name, 1, strlen($name))] = 'desc';
                    continue;
                }

                $parsed[$name] = 'asc';
            }
        }

        return $parsed;
    }
}
