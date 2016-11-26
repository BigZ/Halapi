
Hypertext Application Language for (REpresentational State Transfer) Application Programming Interfaces
-------------------------------------------------------------------------------------------------------

If you're using symfony; use Bigz/HalApiBundle to make use of the services definition

If not, here's how to use it

```
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

function SerializeWithHal(Entity $entity)
{
    $linksRelation = new LinksRelation(
            RouterInterface $router,
            Reader $annotationReader,
            EntityManagerInterface $entityManager,
            RequestStack $requestStack
    );
    $embeddedRelation = new EmbeddedRelation(
            RouterInterface $router,
            Reader $annotationReader,
            EntityManagerInterface $entityManager,
            RequestStack $requestStack
    );

    $relationFactory = new RelationFactory([$linksRelation, $embeddedRelation]);
    $builder = new HALAPIBuilder($relationFactory);

    return $builder->gerSerializer()->serialize($entity);
}
```