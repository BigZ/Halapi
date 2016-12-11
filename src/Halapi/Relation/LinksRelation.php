<?php

namespace Halapi\Relation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Collections\Collection;

/**
 * Class LinksRelation.
 *
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
     * @param \ReflectionProperty $property
     * @param object              $relationContent
     *
     * @return string|null
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function getRelationLink($property, $relationContent)
    {
        /**
         * @var Annotation
         */
        foreach ($this->annotationReader->getPropertyAnnotations($property) as $annotation) {
            if (isset($annotation->targetEntity)) {
                $shortName = strtolower((new \ReflectionClass($annotation->targetEntity))->getShortName());

                return $this->urlGenerator->generate(
                    'get_'.$shortName,
                    [$shortName => $this->getEntityId($relationContent)]
                );
            }
        }

        return;
    }

    /**
     * Get the url of an entity based on the 'get_entity' route pattern.
     *
     * @param $resource
     * @param \ReflectionClass $reflectionClass
     *
     * @return array|null
     */
    private function getSelfLink($resource, $reflectionClass)
    {
        if ($resource instanceof \Traversable) {
            return;
        }

        return [
            'self' => $this->urlGenerator->generate(
                'get_'.strtolower($reflectionClass->getShortName()),
                [strtolower($reflectionClass->getShortName()) => $this->getEntityId($resource)]
            ),
        ];
    }

    /**
     * Get the links of a collection.
     *
     * @param \ReflectionProperty $property
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

    /**
     * Returns entity single identifier.
     * This is a compatibility-limiting feature as it will not be able to get the identity
     * of an entity which has multiple identifiers.
     *
     * @param $entity
     */
    private function getEntityId($entity)
    {
        $meta = $this->entityManager->getClassMetadata(get_class($entity));
        $identifier = $meta->getIdentifier()[0];
        $getter = 'get'.ucfirst($identifier);

        return $entity->$getter();
    }
}
