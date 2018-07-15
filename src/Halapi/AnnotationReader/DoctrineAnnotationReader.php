<?php

namespace Halapi\Relation;

use Halapi\Annotation\Embeddable;
use Doctrine\Common\Annotations\Reader;

/**
 * Reads annotations
 * @author Romain Richard
 */
class DoctrineAnnotationReader implements AnnotationReaderInterface
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param \ReflectionProperty $property
     * @return mixed
     * @throws \ReflectionException
     */
    private function getAssociationRouteName(\ReflectionProperty $property)
    {
        /**
         * @var $annotation Embeddable
         */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Embeddable::class);

        if ($annotation && $annotation->getRouteName()) {
            return $annotation->getRouteName();
        }

        return $this->getResourceRouteName(new \ReflectionClass(
            $this->classMetadata->getAssociationTargetClass($property->getName())
        ));
    }

    /**
     * Return the configured route name for a resource, or get_*entityShortName* by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    private function getResourceRouteName(\ReflectionClass $resource)
    {
        /**
         * @var $annotation Embeddable
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
         * @var $annotation Embeddable
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
