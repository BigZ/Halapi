<?php

namespace Halapi\Relation;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class EmbeddedRelation extends AbstractRelation implements RelationInterface
{
    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * EmbeddedRelation constructor.
     *
     * @param RouterInterface        $router
     * @param Reader                 $annotationReader
     * @param EntityManagerInterface $entityManager
     * @param RequestStack           $requestStack
     */
    public function __construct(
        RouterInterface $router,
        Reader $annotationReader,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        parent::__construct($router, $annotationReader, $entityManager, $requestStack);
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

        $embed = $request->query->get('embed');

        if (!is_array($embed)) {
            return [];
        }

        return $embed;
    }
}
