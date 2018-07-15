<?php

namespace Halapi\AnnotationReader;

/**
 * Reads annotations
 * @author Romain Richard
 */
interface AnnotationReaderInterface
{
    /**
     * Return the configured route name for an embeddable relation.
     *
     * @param \ReflectionProperty $property
     *
     * @return string
     */
    public function getAssociationRouteName(\ReflectionProperty $property);

    /**
     * Return the configured route name for a resource, or get_*entityShortName* by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    public function getResourceRouteName(\ReflectionClass $resource);

    /**
     * Return the configured route name for a resource collection, or get_*entityShortName*s by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    public function getResourceCollectionRouteName(\ReflectionClass $resource);

    /**
     * Does an entity's property have the @embeddable annotation ?
     *
     * @param $property
     *
     * @return bool
     */
    public function isEmbeddable($property);
}
