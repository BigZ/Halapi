<?php

namespace Halapi\Relation;

use Halapi\Annotation\Embeddable;
use Doctrine\Common\Annotations\Reader;

/**
 * Class AbstractRelation.
 *
 * @author Romain Richard
 */
class AbstractRelation
{
    /**
     * @var Reader
     */
    protected $annotationReader;

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
