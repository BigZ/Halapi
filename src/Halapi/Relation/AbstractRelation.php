<?php

namespace Halapi\Relation;

use Halapi\Annotation\Embeddable;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AbstractRelation
 * @author Romain Richard
 */
class AbstractRelation
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * AbstractRelation constructor.
     *
     * @param RouterInterface        $router
     * @param Reader                 $annotationReader
     * @param EntityManagerInterface $entityManager
     * @param RequestStack           $requestStack
     */
    public function __construct(
        RouterInterface $router,
        Reader $annotationReader,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->router = $router;
        $this->annotationReader = $annotationReader;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Does an entity's property has the @embeddable annotation ?
     *
     * @param $property
     *
     * @return bool
     */
    protected function isEmbeddable($property)
    {
        return null !== $this->annotationReader->getPropertyAnnotation($property, Embeddable::class);
    }
}
