<?php

namespace Halapi\Relation;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class EmbeddedRelation.
 *
 * @author Romain Richard
 */
class EmbeddedRelation extends AbstractRelation implements RelationInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * EmbeddedRelation constructor.
     *
     * @param Reader        $annotationReader
     * @param ObjectManager $objectManager
     * @param RequestStack  $requestStack
     */
    public function __construct(
        Reader $annotationReader,
        ObjectManager $objectManager,
        RequestStack $requestStack
    ) {
        $this->annotationReader = $annotationReader;
        $this->objectManager = $objectManager;
        $this->requestStack = $requestStack;
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '_embedded';
    }

    /**
     * {@inheritdoc}
     */
    public function getRelation($resource)
    {
        $reflectionClass = new \ReflectionClass($resource);
        $embedded = [];
        $requestedEmbedded = $this->getEmbeddedParams();

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($this->isEmbeddable($property) && $this->isEmbeddedRequested($propertyName, $requestedEmbedded)) {
                $embedded[$property->getName()] = $this->getEmbeddedContent($resource, $property);
            }
        }

        return $embedded;
    }

    /**
     * @param $propertyName
     * @param $requestedEmbedded
     *
     * @return bool
     */
    private function isEmbeddedRequested($propertyName, $requestedEmbedded)
    {
        return in_array($propertyName, $requestedEmbedded);
    }

    /**
     * @param $resource
     * @param $property
     *
     * @return array
     */
    private function getEmbeddedContent($resource, $property)
    {
        $value = $resource->{'get'.ucfirst($property->getName())}();

        return $this->serializer->toArray($value);
    }

    /**
     * Get the embed query param.
     *
     * @return array
     */
    private function getEmbeddedParams()
    {
        $request = $this->requestStack->getMasterRequest();

        $embed = $request->get('embed');

        if (!is_array($embed)) {
            return [];
        }

        return $embed;
    }
}
