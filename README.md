Hypertext Application Language for (REpresentational State Transfer) Application Programming Interfaces
-------------------------------------------------------------------------------------------------------

[![Build
Status](https://travis-ci.org/BigZ/Halapi.svg?branch=master)](http://travis-ci.org/BigZ/Halapi)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/240ef51f-6625-4c79-9ba2-58d4fcb63fa5/mini.png)](https://insight.sensiolabs.com/projects/240ef51f-6625-4c79-9ba2-58d4fcb63fa5)
[![Scrutinizer Quality
Score](https://scrutinizer-ci.com/g/BigZ/Halapi/badges/quality-score.png?s=45b5a825f99de4d29c98b5103f59e060139cf354)](https://scrutinizer-ci.com/g/BigZ/Halapi/)

This library is a tighly coupled to Doctrine ORM & Symfony's Router implementation of an HAL formatter.

Given some conventions, displaying the HAL representation of any entity becomes vert easy.

If you're using symfony; use Bigz/HalApiBundle to make use of the services definition

If not, here's how to use it

```
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

public function __construct(
    RouterInterface $router,
    Reader $annotationReader,
    EntityManagerInterface $entityManager,
    RequestStack $requestStack
) {
    $this->router = $router;
    $this->annotationReader = $annotationReader;
    $this->entityManager = $entityManager;
    $this->requestStack = $requestStack;
}

public function SerializeEntityWithHal(Entity $entity)
{
    $linksRelation = new LinksRelation(
        $this->router,
        $this->annotationReader,
        $this->entityManager,
        $this->requestStack
    );
    $embeddedRelation = new EmbeddedRelation(
        $this->router,
        $this->annotationReader,
        $this->entityManager,
        $this->requestStack
    );

    $relationFactory = new RelationFactory([$linksRelation, $embeddedRelation]);
    $builder = new HALAPIBuilder($relationFactory);

    return $builder->gerSerializer()->serialize($entity);
}

public function SerializePaginatedCollectionWithHal($entityName)
{
    $linksRelation = new LinksRelation(
        $this->router,
        $this->annotationReader,
        $this->entityManager,
        $this->requestStack
    );
    $embeddedRelation = new EmbeddedRelation(
        $this->router,
        $this->annotationReader,
        $this->entityManager,
        $this->requestStack
    );

    $relationFactory = new RelationFactory([$linksRelation, $embeddedRelation]);
    $builder = new HALAPIBuilder($relationFactory);

    $paginationFactory = new PaginationFactory(
        $this->router,
        $this->annotationReader,
        $this->entityManager
    );
    $paginatedRepresentation = $paginationFactory->getRepresentation($entityName);

    return $builder->gerSerializer()->serialize($paginatedRepresentation);
}
```
