<?php

namespace Halapi\Relation;

use Halapi\AnnotationReader\AnnotationReaderInterface;
use JMS\Serializer\SerializerBuilder;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class EmbeddedRelation.
 *
 * @author Romain Richard
 */
class EmbeddedRelation implements RelationInterface
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * EmbeddedRelation constructor.
     *
     * @param AnnotationReaderInterface $annotationReader
     * @param ServerRequestInterface    $request
     */
    public function __construct(
        AnnotationReaderInterface $annotationReader,
        ServerRequestInterface $request
    ) {
        $this->annotationReader = $annotationReader;
        $this->request = $request;
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

            if ($this->annotationReader->isEmbeddable($property)
                && $this->isEmbeddedRequested($propertyName, $requestedEmbedded)
            ) {
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
        $queryParams = $this->request->getQueryParams();

        if (isset($queryParams['include']) && $queryParams['include']) {
            return explode(',', $queryParams['include']);
        }

        return [];
    }
}
