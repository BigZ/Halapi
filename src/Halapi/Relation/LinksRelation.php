<?php

namespace Halapi\Relation;

use Doctrine\Common\Collections\Collection;

/**
 * Class LinksRelation
 * @author Romain Richard
 */
class LinksRelation extends AbstractRelation implements RelationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '_links';
    }

    /**
     * {@inheritdoc}
     */
    public function getRelation($resource)
    {
        $reflectionClass = new \ReflectionClass($resource);
        $links = $this->getSelfLink($resource, $reflectionClass);

        foreach ($reflectionClass->getProperties() as $property) {
            if ($this->isEmbeddable($property) && $property->getName()) {
                $propertyName = $property->getName();
                $relationContent = $resource->{'get'.ucfirst($propertyName)}();
                $links[$propertyName] = $this->getRelationLinks($property, $relationContent);

                if (!$links[$propertyName]) {
                    unset($links[$propertyName]);
                }
            }
        }

        return $links;
    }

    /**
     * @param string $property
     * @param object $relationContent
     * @return string|null
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function getRelationLink($property, $relationContent)
    {
        $meta = $this->entityManager->getClassMetadata(get_class($relationContent));
        $identifier = $meta->getSingleIdentifierFieldName();

        foreach ($this->annotationReader->getPropertyAnnotations($property) as $annotation) {
            if (isset($annotation->targetEntity)) {
                try {
                    $id = $this->entityManager->getUnitOfWork()->getEntityIdentifier($relationContent)[$identifier];

                    return $this->router->generate(
                        'get_'.strtolower($annotation->targetEntity),
                        [strtolower($annotation->targetEntity) => $id]
                    );
                } catch (\Exception $exception) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Get the url of an entity based on the 'get_entity' route pattern.
     *
     * @param $resource
     * @param $reflectionClass
     *
     * @return array|void
     */
    private function getSelfLink($resource, $reflectionClass)
    {
        if ($resource instanceof \Traversable) {
            return;
        }

        try {
            return [
                'self' => $this->router->generate(
                    'get_'.strtolower($reflectionClass->getShortName()),
                    [strtolower($reflectionClass->getShortName()) => $resource->getId()]
                ),
            ];
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * Get the links of a collection.
     *
     * @param $property
     * @param $relationContent
     *
     * @return array|void
     */
    private function getRelationLinks($property, $relationContent)
    {
        if ($relationContent instanceof Collection) {
            $links = [];

            foreach ($relationContent as $relation) {
                $links[] = $this->getRelationLink($property, $relation);
            }

            return $links;
        }

        return $this->getRelationLink($property, $relationContent);
    }
}
