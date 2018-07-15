<?php

namespace Halapi\Relation;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Halapi\AnnotationReader\AnnotationReaderInterface;
use Halapi\ObjectManager\ObjectManagerInterface;
use Halapi\UrlGenerator\UrlGeneratorInterface;

/**
 * Class LinksRelation.
 *
 * @author Romain Richard
 */
class LinksRelation implements RelationInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var ObjectManagerInterface
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
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * LinksRelation constructor.
     *
     * @param AnnotationReaderInterface $annotationReader
     * @param UrlGeneratorInterface     $urlGenerator
     * @param ObjectManagerInterface    $objectManager
     */
    public function __construct(
        AnnotationReaderInterface $annotationReader,
        UrlGeneratorInterface $urlGenerator,
        ObjectManagerInterface $objectManager
    ) {
        $this->annotationReader = $annotationReader;
        $this->urlGenerator = $urlGenerator;
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
     * @param object $resource
     * @return array|null|string
     * @throws \ReflectionException
     */
    public function getRelation($resource)
    {
        $this->classMetadata = $this->objectManager->getClassMetadata(get_class($resource));
        $this->reflectionClass = new \ReflectionClass($resource);
        $links = $this->getSelfLink($resource);

        foreach ($this->reflectionClass->getProperties() as $property) {
            if ($this->annotationReader->isEmbeddable($property) && $property->getName()) {
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
     * @param $relationContent
     * @return mixed
     * @throws \ReflectionException
     */
    private function getRelationLink(\ReflectionProperty $property, $relationContent)
    {
        $relationReflection = new \ReflectionClass($relationContent);
        if ($this->classMetadata->hasAssociation($property->getName())) {
            return $this->urlGenerator->generate(
                $this->annotationReader->getAssociationRouteName($property),
                [
                    strtolower($relationReflection->getShortName()) => $this->objectManager->getIdentifier($relationContent),
                ]
            );
        }
    }

    /**
     * Get the url of an entity.
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

        return [
            'self' => $this->urlGenerator->generate(
                $this->annotationReader->getResourceRouteName($this->reflectionClass),
                [
                    strtolower($this->reflectionClass->getShortName()) => $this->objectManager->getIdentifier($resource),
                ]
            ),
        ];
    }

    /**
     * @param \ReflectionProperty $property
     * @param $relationContent
     * @return array|mixed
     * @throws \ReflectionException
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
}
