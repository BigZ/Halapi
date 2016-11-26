<?php

namespace Halapi\Factory;

use Halapi\Representation\PaginatedRepresentation;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PaginationFactory.
 *
 * @author Romain Richard
 */
class PaginationFactory
{
    /**
     * @var EntityManagerInterface
     */
    public $entityManager;

    /**
     * @var RouterInterface
     */
    public $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * PaginationFactory constructor.
     *
     * @param RouterInterface        $router
     * @param EntityManagerInterface $entityManager
     * @param RequestStack           $requestStack
     */
    public function __construct(
        RouterInterface $router,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Get a paginated representation of a collection of entities.
     *
     * @param string $className
     *
     * @return PaginatedRepresentation
     */
    public function getRepresentation($className)
    {
        $repository = $this->entityManager->getRepositobry($className);
        list($page, $limit, $sorting, $filterValues, $filerOperators) = array_values($this->addPaginationParams());
        $queryBuilder = $repository->findAllSorted($sorting, $filterValues, $filerOperators);
        $shortName = (new \ReflectionClass($className))->getShortName();

        $pagerAdapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($pagerAdapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return new PaginatedRepresentation(
            $page,
            $limit,
            [
                'self' => $this->getPaginatedRoute($shortName, $limit, $page, $sorting),
                'first' => $this->getPaginatedRoute($shortName, $limit, 1, $sorting),
                'next' => $this->getPaginatedRoute(
                    $shortName,
                    $limit,
                    $page < $pager->getNbPages() ? $page + 1 : $pager->getNbPages(),
                    $sorting
                ),
                'last' => $this->getPaginatedRoute($shortName, $limit, $pager->getNbPages(), $sorting),
            ],
            (array) $pager->getCurrentPageResults()
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

        $request = $this->requestStack->getMasterRequest();

        return $resolver->resolve(array_filter([
            'page' => $request->query->get('page'),
            'limit' => $request->query->get('limit'),
            'sorting' => $request->query->get('sorting'),
            'filtervalue' => $request->query->get('filtervalue'),
            'filteroperator' => $request->query->get('filteroperator'),
        ]));
    }

    /**
     * Return the url of a resource based on the 'get_entity' route name convention.
     *
     * @param $name
     * @param $limit
     * @param $page
     * @param $sorting
     *
     * @return mixed
     */
    private function getPaginatedRoute($name, $limit, $page, $sorting)
    {
        return $this->router->generate(
            'get_'.strtolower($name).'s',
            [
                'sorting' => $sorting,
                'page' => $page,
                'limit' => $limit,
            ]
        );
    }
}
