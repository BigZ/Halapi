<?php

namespace Halapi\Pager;

use Pagerfanta\Adapter\AdapterInterface;

/**
 * Class PagerFanta
 * @author Romain Richard
 */
class PagerFanta implements PagerInterface
{
    /**
     * @var \Pagerfanta\Pagerfanta
     */
    private $pager;

    /**
     * @var string
     */
    private $pagerStrategy;

    /**
     * PagerFanta constructor.
     * @param string $pagerStrategy
     * @return PagerFanta
     */
    public function __construct($pagerStrategy)
    {
        $this->setPagerStrategy($pagerStrategy);
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getCurrentPageResults()
    {
        return $this->pager->getCurrentPageResults();
    }

    /**
     * @inheritdoc
     */
    public function getPageCount()
    {
        $this->pager->getNbPages();
    }

    /**
     * @inheritdoc
     * @param mixed $results
     */
    public function setResults($results)
    {
        $pagerAdapter = $this->getPagerAdapter($results);
        $this->pager = new \Pagerfanta\Pagerfanta($pagerAdapter);
    }

    /**
     * @inheritdoc
     * @param int $max
     */
    public function setMaxPerPage($max)
    {
        $this->pager->setMaxPerPage($max);
    }

    /**
     * @inheritdoc
     * @param int $page
     */
    public function setCurrentPage($page)
    {
        $this->pager->setCurrentPage($page);
    }

    /**
     * @param string $pagerStrategy
     */
    public function setPagerStrategy($pagerStrategy)
    {
        if (!class_exists('Pagerfanta\Adapter\\'.$pagerStrategy.'Adapter')) {
            throw new \InvalidArgumentException(sprintf(
                'No adapter named %s found in %s namespace',
                $pagerStrategy.'Adapter',
                'Pagerfanta\Adapter'
            ));
        }

        $this->pagerStrategy = $pagerStrategy;
    }

    /**
     * @param array $results
     *
     * @return AdapterInterface
     */
    private function getPagerAdapter($results)
    {
        $adapterClassName = 'Pagerfanta\Adapter\\'.$this->pagerStrategy.'Adapter';

        return new $adapterClassName(...$results);
    }
}
