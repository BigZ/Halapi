<?php

namespace Halapi\Relation;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class LinksRelation.
 *
 * @author Romain Richard
 */
class LinksRelation extends AbstractRelation implements RelationInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * AbstractRelation constructor.
     *
     * @param Reader                $annotationReader
     * @param UrlGeneratorInterface $urlGenerator
     * @param ObjectManager         $objectManager
     */
    public function __construct(
        Reader $annotationReader,
        UrlGeneratorInterface $urlGenerator,
        ObjectManager $objectManager
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->annotationReader = $annotationReader;
        $this->objectManager = $objectManager;
    }

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
        $this->classMetadata = $this->objectManager->getClassMetadata(get_class($resource));
        $this->reflectionClass = new \ReflectionClass($resource);
        $links = $this->getSelfLink($resource);

        foreach ($this->reflectionClass->getProperties() as $property) {
            if ($this->isEmbeddable($property) && $property->getName()) {
                $propertyName = $property->getName();
                $relationContent = $resource->{'get'.ucfirst($propertyName)}();
                if ($relationContent) {
                    $links[$propertyName] = $this->getRelationLinks($property, $relationContent);
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
    private function getRelationLink(\ReflectionProperty $property, $relationContent)
    {
        if ($this->classMetadata->hasAssociation($property->getName())) {
            $targetClass = $this->classMetadata->getAssociationTargetClass($property->getName());
            $shortName = strtolower((new \ReflectionClass($targetClass))->getShortName());

            return $this->urlGenerator->generate(
                'get_'.$shortName,
                [$shortName => $this->getEntityId($relationContent)]
            );
        }
    }

    /**
     * Get the url of an entity based on the 'get_entity' route pattern.
     *
     * @param $resource
     * @param \ReflectionClass $reflectionClass
     *
     * @return array|null
     */
    private function getSelfLink($resource)
    {
        if ($resource instanceof \Traversable) {
            return;
        }

        return [
            'self' => $this->urlGenerator->generate(
                'get_'.strtolower($this->reflectionClass->getShortName()),
                [strtolower($this->reflectionClass->getShortName()) => $this->getEntityId($resource)]
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
    private function getRelationLinks(\ReflectionProperty $property, $relationContent)
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
        $identifier = $this->classMetadata->getIdentifier()[0];
        $getter = 'get'.ucfirst($identifier);

        return $entity->$getter();
    }
}
