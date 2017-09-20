Hypertext Application Language for (REpresentational State Transfer) Application Programming Interfaces
-------------------------------------------------------------------------------------------------------

[![Build
Status](https://travis-ci.org/BigZ/Halapi.svg?branch=master)](http://travis-ci.org/BigZ/Halapi)
[![Test Coverage](https://codeclimate.com/github/BigZ/Halapi/badges/coverage.svg)](https://codeclimate.com/github/BigZ/Halapi/coverage)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/240ef51f-6625-4c79-9ba2-58d4fcb63fa5/mini.png)](https://insight.sensiolabs.com/projects/240ef51f-6625-4c79-9ba2-58d4fcb63fa5)
[![Scrutinizer Quality
Score](https://scrutinizer-ci.com/g/BigZ/Halapi/badges/quality-score.png?s=45b5a825f99de4d29c98b5103f59e060139cf354)](https://scrutinizer-ci.com/g/BigZ/Halapi/)
[![Code Climate](https://codeclimate.com/github/BigZ/Halapi/badges/gpa.svg)](https://codeclimate.com/github/BigZ/Halapi)

Given some conventions, displaying the HAL representation of any entity becomes very easy.

HAL is a json presentation format of the HATEOAS constraint, which is meant to add relations between objects.

It's whole specification is available here http://stateless.co/hal_specification.html

The work is in progress to make it framework agnostic but actually relies on you using symfony/http-foundation, which will change in a close future to use psr6 (while providing a bridge)
For the object manager, you are free to choose the one you like, although only doctrine orm has been implemented at the mement.
Relation findings relies also a lot on doctrine's ClassMetadata interface, that we should maybe abstract (you can still use your own implementaion)

# Usage
`composer req bigz/halapi`

## Symfony bundle
https://github.com/BigZ/HalapiBundle

## Full fledged example using symfony (a good starting point for your api)
https://github.com/BigZ/promote-api

# Example

```
use Doctrine\Common\Annotations\Reader;
use Halapi\ObjectManager\ObjectManagerInterface;
use Halapi\UrlGenerator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class EntityController()
{
    /**
     * You can provide your own implementations of those interfaces or use the provided ones.
     */
    public function __construct(
        UrlGeneratorInterface $router,
        Reader $annotationReader,
        ObjectManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->router = $router;
        $this->annotationReader = $annotationReader;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Accessed by the /entities/{id} route
     */
    public function getHalFormattedEntity(Entity $entity)
    {
        $linksRelation = new LinksRelation(
            $this->router,
            $this->annotationReader,
            $this->entityManager,
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

    /**
     * Accessed by the /entities
     */
    public function getHalFormattedCollection($entityName)
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
}
```

## Resources

### List

#### Pagination
A list will give you a paginated ressource, HAL formatted.

`/entities?page=2&limite&sorting[id]=desc`

#### Filtering
You can filter out results on specific fields.

`/entities?filtervalue[id]=5&filteroperator[id]=>`

Available operators are `>`, `<`, `>=`, `<=`, `=`, `!=`


#### Sorting
You can sort the result by any property

`/entities?sorting[id]=asc`

### Entity
#### Creating new entities
`POST /entities`

`{
     "entity": {
         "name": "eminem",
         "slug": "eminem",
         "bio": "rapper from detroit",
         "labels": [1, 2]
     }
 }`

 will return

`{
   "id": 2,
   "name": "eminem",
   "slug": "eminem",
   "bio": "rapper from detroit",
   "_links": {
     "self": "/artists/2",
     "labels": [
       "/labels/1",
       "/labels/2"
     ]
   }
 }`

PUT & PATCH works the same way

#### Embedding

By default, relations are not embeded. You can change this behaviour by specifiying wich embedeed entities you need.

`/entities/1?embed[]=gigs&embed[]=labels`

# Roadmap

- Move from symfony/http-foundation to psr6
- Provide a custom interface for the annotation reader - or should we support many types of configuration ?
- Untie from Jms serializer
- Move from array type parameters such as filtervalue[id] to something in compatible with the openapi specs
- Be able to chose output format: HAL, JsonApi, ...