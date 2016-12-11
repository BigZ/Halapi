<?php

namespace Halapi\Representation;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

/**
 * Add a Rest features to entity repositories
 * Class RestEntityRepositoryTrait.
 */
trait RestEntityRepositoryTrait
{
    /**
     * @return ClassMetadata
     */
    abstract public function getClassMetadata();

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $alias
     * @param string $indexBy The index for the from.
     *
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    /**
     * @param array $sorting
     * @param array $filterValues
     * @param array $filerOperators
     *
     * @return QueryBuilder
     */
    public function findAllSorted(array $sorting, array $filterValues, array $filerOperators)
    {
        $fields = array_keys($this->getClassMetadata()->fieldMappings);
        $queryBuilder = $this->createQueryBuilder('e');

        foreach ($fields as $field) {
            if (isset($sorting[$field])) {
                $direction = ($sorting[$field] === 'asc') ? 'asc' : 'desc';
                $queryBuilder->addOrderBy('e.'.$field, $direction);
            }

            if (isset($filterValues[$field])) {
                $operator = '=';

                if (isset($filerOperators[$field])
                    && in_array($filerOperators[$field], ['>', '<', '>=', '<=', '=', '!='])
                ) {
                    $operator = $filerOperators[$field];
                }

                $queryBuilder->andWhere('e.'.$field.$operator."'".$filterValues[$field]."'");
            }
        }

        return $queryBuilder;
    }
}
