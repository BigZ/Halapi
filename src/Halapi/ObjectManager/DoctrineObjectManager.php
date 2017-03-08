<?php

namespace Halapi\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Interface ObjectManagerInterface
 * @author Romain Richard
 */
class DoctrineObjectManager implements ObjectManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * DoctrineObjectManager constructor.
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param object $resource
     * @return mixed
     */
    public function getIdentifierName($resource)
    {
        $classMetadata = $this->objectManager->getClassMetadata(get_class($resource));

        return $classMetadata->getIdentifier()[0];
    }

    /**
     * @param object $resource
     * @return mixed
     */
    public function getIdentifier($resource)
    {
        $identifier = new \ReflectionProperty($resource, $this->getIdentifier($resource));
        if ($identifier->isPublic()) {
            return $identifier;
        }

        $getter = 'get'.ucfirst($identifier);
        $getterReflection = new \ReflectionMethod($resource, $getter);
        if (method_exists($resource, $getter) && $getterReflection->isPublic()) {
            return $resource->$getter();
        }

        return;
    }

    /**
     * @param string $className
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className)
    {
        return $this->objectManager->getRepository($className);
    }

    /**
     * @param string $className
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata($className)
    {
        return $this->objectManager->getClassMetadata($className);
    }
}
