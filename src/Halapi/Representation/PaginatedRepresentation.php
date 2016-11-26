<?php

namespace Halapi\Representation;

/**
 * A paginated collection representation.
 *
 * Class PaginatedRepresentation
 */
class PaginatedRepresentation
{
    /**
     * Filters const used to provide easy documentation integration.
     */
    const FILTERS = [
        ['name' => 'page', 'dataType' => 'integer', 'default' => 1],
        ['name' => 'limit', 'dataType' => 'integer', 'default' => 20],
        // We'll need some more work to re-implement that as OpenApi has currently no support for
        // query params of this kind. ( ?param[key]=value )
        //['name' => 'sorting', 'dataType' => 'array'],
        //['name' => 'filtervalue', 'dataType' => 'array', 'pattern' => '[field]=(asc|desc)'],
        //['name' => 'filteroperator', 'dataType' => 'array', 'pattern' => '[field]=(<|>|<=|>=|=|!=)'],
    ];

    /**
     * @var string
     */
    public $page;

    /**
     * @var string
     */
    public $limit;

    /**
     * @var object
     */
    public $_links;

    /**
     * @var array
     */
    public $_embedded;

    /**
     * PaginatedRepresentation constructor.
     *
     * @param int   $page
     * @param int   $limit
     * @param array $links
     * @param array $embedded
     */
    public function __construct($page, $limit, $links, $embedded)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->_links = $links;
        $this->_embedded = $embedded;
    }
}
