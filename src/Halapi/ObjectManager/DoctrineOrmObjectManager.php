<?php

namespace Halapi\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Interface ObjectManagerInterface
 * @author Romain Richard
 */
class DoctrineOrmObjectManager implements ObjectManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * DoctrineOrmObjectManager constructor.
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
        $identifier = new \ReflectionProperty($resource, $this->getIdentifierName($resource));
        if ($identifier->isPublic()) {
            return $identifier;
        }

        $getter = 'get'.ucfirst($identifier->getName());
        $getterReflection = new \ReflectionMethod($resource, $getter);
        if (method_exists($resource, $getter) && $getterReflection->isPublic()) {
            return $resource->$getter();
        }

        return;
    }

    /**
     * @param string $className
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata($className)
    {
        return $this->objectManager->getClassMetadata($className);
    }

    /**
     * @param string $className
     * @param array  $sorting
     * @param array  $filterValues
     * @param array  $filerOperators
     *
     * @return array
     */
    public function findAllSorted($className, array $sorting, array $filterValues, array $filerOperators)
    {
        $fields = array_keys($this->getClassMetadata($className)->fieldMappings);
        $repository = $this->objectManager->getRepository($className);

        // If user's own implementation is defined, use it
        try {
            return $repository->findAllSorted($className, $sorting, $filterValues, $filerOperators);
        } catch (\BadMethodCallException $exception) {
            $queryBuilder = $repository->createQueryBuilder('e');

            foreach ($fields as $field) {
                if (isset($sorting[$field])) {
                    $direction = ($sorting[$field] === 'asc') ? 'asc' : 'desc';
                    $queryBuilder->addOrderBy('e.'.$field, $direction);
                }

                if (isset($filterValues[$field])) {
                    $operator = '=';

                    if (isset($filerOperators[$field])
                        && in_array($filerOperators[$field], ['>', '<', '>=', '<=', '=', '!='])
                    ) {
                        $operator = $filerOperators[$field];
                    }

                    $queryBuilder->andWhere('e.'.$field.$operator."'".$filterValues[$field]."'");
                }
            }

            return [$queryBuilder];
        }
    }
}
