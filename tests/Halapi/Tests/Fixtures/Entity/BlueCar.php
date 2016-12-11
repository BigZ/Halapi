<?php

namespace Halapi\Tests\Fixtures\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Halapi\Annotation\Embeddable;

/**
 * Class BlueCar
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
    public function setDoors(ArrayCollection $doors)
    {
        $this->doors = $doors;
    }
    
}