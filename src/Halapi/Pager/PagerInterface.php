<?php

namespace Halapi\Pager;

/**
 * Class PagerInterface
 * @author Romain Richard
 */
interface PagerInterface
{
    /**
     * Returns the results for the current page.
     *
     * @return array|\Traversable
     */
    public function getCurrentPageResults();

    /**
     * Get the number of pages of the current result set.
     *
     * @return int
     */
    public function getPageCount();

    /**
     * Set the global result collection.
     * It can be of many types, for instance array or Doctrine\ORM\Query,
     * depending on which adapter you use.
     *
     * @param mixed $results
     * @return mixed
     */
    public function setResults($results);

    /**
     * Set the maximum number of results per page (aka limit).
     *
     * @param int $max
     * @return void
     */
    public function setMaxPerPage($max);

    /**
     * Set the page to display.
     *
     * @param int $page
     * @return void
     */
    public function setCurrentPage($page);
}
