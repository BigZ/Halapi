<?php

namespace Halapi\Relation;

/**
 * Interface RelationInterface
 * @package Halapi\Relation
 */
interface RelationInterface
{
    /**
     * Return the name of the relation, used as the array key of the representation.
     * Be sure to choose something kind-of unique, and by convention starting by an underscore.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the content of the relation.
     *
     * @param object $resource
     *
     * @return null|string|array
     */
    public function getRelation($resource);
}
