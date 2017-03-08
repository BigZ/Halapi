<?php

namespace Halapi\ObjectManager;

/**
 * Interface ObjectManagerInterface
 * @author Romain Richard
 */
interface ObjectManagerInterface
{
    /**
     * @param object $resource
     * @return mixed
     */
    public function getIdentifier($resource);

    /**
     * @param object $resource
     * @return mixed
     */
    public function getIdentifierName($resource);

    /**
     * @param string $className
     * @param array  $sorting
     * @param array  $filterValues
     * @param array  $filerOperators
     *
     * @return array
     */
    public function findAllSorted($className, array $sorting, array $filterValues, array $filerOperators);

    /**
     * @param string $className
     * @return mixed
     */
    public function getClassMetadata($className);
}
