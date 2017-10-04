<?php

namespace Halapi\Tests\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Halapi\Annotation\Embeddable;

/**
 * Class BlueCar.
 *
 * @author Romain Richard
 */
class BlueCar
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ArrayCollection
     * @Embeddable()
     */
    private $doors;

    /**
     * @var Engine
     * @Embeddable()
     */
    private $engine;

    /**
     * BlueCar constructor.
     * @param int|null             $id
     * @param ArrayCollection|null $doors
     * @param Engine|null          $engine
     */
    public function __construct($id = null, $doors = null, $engine = null)
    {
        $this->setId($id);
        $this->setDoors($doors);
        $this->setEngine($engine);
    }

    /**
     * @return int
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
     * @return ArrayCollection
     */
    public function getDoors()
    {
        return $this->doors;
    }

    /**
     * @param ArrayCollection $doors
     */
    public function setDoors(ArrayCollection $doors = null)
    {
        $this->doors = $doors;
    }

    /**
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param Engine $engine
     */
    public function setEngine(Engine $engine = null)
    {
        $this->engine = $engine;
    }
}
