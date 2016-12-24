<?php

namespace Halapi\Relation;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Annotations\Reader;
use Halapi\Annotation\Embeddable;
use Halapi\UrlGenerator\UrlGeneratorInterface;

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
            $identifier = $this->getIdentifier($relationContent);

            return $this->urlGenerator->generate(
                $this->getAssociationRouteName($property),
                [$identifier => $this->getId($relationContent, $identifier)]
            );
        }
    }

    /**
     * Get the url of an entity based on the 'get_entity' route pattern.
     *
     * @param $resource
     *
     * @return array|null
     */
    private function getSelfLink($resource)
    {
        if ($resource instanceof \Traversable) {
            return;
        }

        $identifier = $this->getIdentifier($resource);

        return [
            'self' => $this->urlGenerator->generate(
                $this->getResourceRouteName($this->reflectionClass),
                [$identifier => $this->getId($resource, $identifier)]
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
     * Return the configured route name for an embeddable relation.
     *
     * @param \ReflectionProperty $property
     *
     * @return string
     */
    private function getAssociationRouteName(\ReflectionProperty $property)
    {
        if ($routeName = $this->annotationReader->getPropertyAnnotation($property, Embeddable::class)->getRouteName()) {
            return $routeName;
        }

        return $this->getResourceRouteName(new \ReflectionClass(
            $this->classMetadata->getAssociationTargetClass($property->getName())
        ));
    }

    /**
     * Return the configured route name for a resource, or get_*entityShortName* by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    private function getResourceRouteName(\ReflectionClass $resource)
    {
        if ($routeName = $this->annotationReader->getClassAnnotation($resource, Embeddable::class)->getRouteName()) {
            return $routeName;
        }

        return 'get_'.strtolower($resource->getShortName());
    }

    /**
     * @param $resource
     *
     * @return mixed
     */
    private function getIdentifier($resource)
    {
        $classMetadata = $this->objectManager->getClassMetadata(get_class($resource));

        return $classMetadata->getIdentifier()[0];
    }

    private function getId($resource, $identifier)
    {
        $id = new \ReflectionProperty($resource, $identifier);
        if ($id->isPublic()) {
            return $id;
        }

        $getter = 'get'.ucfirst($identifier);
        $getterReflection = new \ReflectionMethod($resource, $getter);
        if (method_exists($resource, $getter) && $getterReflection->isPublic()) {
            return $resource->$getter();
        }

        return;
    }
}
