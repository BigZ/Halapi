<?php

namespace Halapi\AnnotationReader;

use Halapi\Annotation\Embeddable;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Reads annotations.
 *
 * @author Romain Richard
 */
class DoctrineAnnotationReader implements AnnotationReaderInterface
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * DoctrineAnnotationReader constructor.
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Get the route name of a relationship.
     *
     * @param \ReflectionProperty $property
     * @param string              $targetClass
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function getAssociationRouteName(\ReflectionProperty $property, $targetClass)
    {
        /**
         * @var Embeddable
         */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Embeddable::class);

        if ($annotation && $annotation->getRouteName()) {
            return $annotation->getRouteName();
        }

        return $this->getResourceRouteName(new \ReflectionClass($targetClass));
    }

    /**
     * Return the configured route name for a resource, or get_*entityShortName* by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    public function getResourceRouteName(\ReflectionClass $resource)
    {
        /**
         * @var Embeddable
         */
        $annotation = $this->annotationReader->getClassAnnotation($resource, Embeddable::class);

        if ($annotation && $annotation->getRouteName()) {
            return $annotation->getRouteName();
        }

        return sprintf('get_%s', strtolower($resource->getShortName()));
    }

    /**
     * Return the configured route name for a resource collection, or get_*entityShortName*s by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    public function getResourceCollectionRouteName(\ReflectionClass $resource)
    {
        /**
         * @var Embeddable
         */
        $annotation = $this->annotationReader->getClassAnnotation($resource, Embeddable::class);

        if ($annotation && $annotation->getCollectionRouteName()) {
            return $annotation->getCollectionRouteName();
        }

        return sprintf('get_%ss', strtolower($resource->getShortName()));
    }

    /**
     * Does an entity's property has the @embeddable annotation ?
     *
     * @param $property
     *
     * @return bool
     */
    public function isEmbeddable($property)
    {
        return null !== $this->annotationReader->getPropertyAnnotation($property, Embeddable::class);
    }
}
