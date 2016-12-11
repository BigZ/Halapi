<?php

namespace Halapi\Tests\Fixtures\Entity;

/**
 * Door fixture.
 *
 * @author Romain Richard
 */
class Door
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $side;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * @param mixed $side
     */
    public function setSide($side)
    {
        $this->side = $side;
    }
}
