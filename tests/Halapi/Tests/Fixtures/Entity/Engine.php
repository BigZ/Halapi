<?php

namespace Halapi\Tests\Fixtures\Entity;

/**
 * Engine fixture.
 *
 * @author Romain Richard
 */
class Engine
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $horses;

    /**
     * @var string
     */
    private $name;

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
     * @return int
     */
    public function getHorses()
    {
        return $this->horses;
    }

    /**
     * @param int $horses
     */
    public function setHorses($horses)
    {
        $this->horses = $horses;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
