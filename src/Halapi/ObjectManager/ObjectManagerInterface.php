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
     * Get the related object repository (which must implement getAllPaginated)
     * @param string $className
     * @return mixed
     */
    public function getRepository($className);

    /**
     * @param string $className
     * @return mixed
     */
    public function getClassMetadata($className);
}
