<?php

namespace Halapi\Relation;

use Doctrine\Common\Persistence\ObjectManager;
use Halapi\Annotation\Embeddable;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractRelation
 * @author Romain Richard
 */
class AbstractRelation
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * AbstractRelation constructor.
     *
     * @param UrlGeneratorInterface  $urlGenerator
     * @param Reader                 $annotationReader
     * @param ObjectManager          $entityManager
     * @param RequestStack           $requestStack
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Reader $annotationReader,
        ObjectManager $entityManager,
        RequestStack $requestStack
    ) {
        $this->urlGenerator = $urlGenerator;
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
