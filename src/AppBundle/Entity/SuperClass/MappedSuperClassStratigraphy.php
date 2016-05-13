<?php

namespace AppBundle\Entity\SuperClass;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\SuperClass\MappedSuperClassStratigraphy as Stratigraphy;
use AppBundle\Entity\SuperClass\MappedSuperClassSpecimen as Specimen;

/**
* @ORM\MappedSuperclass()
* @ORM\Table(name="Stratigraphies")
*/
abstract class MappedSuperClassStratigraphy
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=10) 
     */
    protected $geologicalcontextid;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $bed;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $earliestageorloweststage;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $earliesteonorlowesteonothem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $earliestepochorlowestseries;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $earliesteraorlowesterathem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $earliestperiodorlowestsystem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $formation;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $group_;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $highestbiostratigraphiczone;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $latestageorhigheststage;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $latesteonorhighesteonothem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $latestepochorhighestseries;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $latesteraorhighesterathem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $latestperiodorhighestsystem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $lowestbiostratigraphiczone;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $member;


    /**
     * Get geologicalcontextid
     *
     * @return integer
     */
    public function getGeologicalcontextid()
    {
        return $this->geologicalcontextid;
    }

    /**
     * Set bed
     *
     * @param string $bed
     *
     * @return Stratigraphy
     */
    public function setBed($bed)
    {
        $this->bed = $bed;

        return $this;
    }

    /**
     * Get bed
     *
     * @return string
     */
    public function getBed()
    {
        return $this->bed;
    }

    /**
     * Set earliestageorloweststage
     *
     * @param string $earliestageorloweststage
     *
     * @return Stratigraphy
     */
    public function setEarliestageorloweststage($earliestageorloweststage)
    {
        $this->earliestageorloweststage = $earliestageorloweststage;

        return $this;
    }

    /**
     * Get earliestageorloweststage
     *
     * @return string
     */
    public function getEarliestageorloweststage()
    {
        return $this->earliestageorloweststage;
    }

    /**
     * Set earliesteonorlowesteonothem
     *
     * @param string $earliesteonorlowesteonothem
     *
     * @return Stratigraphy
     */
    public function setEarliesteonorlowesteonothem($earliesteonorlowesteonothem)
    {
        $this->earliesteonorlowesteonothem = $earliesteonorlowesteonothem;

        return $this;
    }

    /**
     * Get earliesteonorlowesteonothem
     *
     * @return string
     */
    public function getEarliesteonorlowesteonothem()
    {
        return $this->earliesteonorlowesteonothem;
    }

    /**
     * Set earliestepochorlowestseries
     *
     * @param string $earliestepochorlowestseries
     *
     * @return Stratigraphy
     */
    public function setEarliestepochorlowestseries($earliestepochorlowestseries)
    {
        $this->earliestepochorlowestseries = $earliestepochorlowestseries;

        return $this;
    }

    /**
     * Get earliestepochorlowestseries
     *
     * @return string
     */
    public function getEarliestepochorlowestseries()
    {
        return $this->earliestepochorlowestseries;
    }

    /**
     * Set earliesteraorlowesterathem
     *
     * @param string $earliesteraorlowesterathem
     *
     * @return Stratigraphy
     */
    public function setEarliesteraorlowesterathem($earliesteraorlowesterathem)
    {
        $this->earliesteraorlowesterathem = $earliesteraorlowesterathem;

        return $this;
    }

    /**
     * Get earliesteraorlowesterathem
     *
     * @return string
     */
    public function getEarliesteraorlowesterathem()
    {
        return $this->earliesteraorlowesterathem;
    }

    /**
     * Set earliestperiodorlowestsystem
     *
     * @param string $earliestperiodorlowestsystem
     *
     * @return Stratigraphy
     */
    public function setEarliestperiodorlowestsystem($earliestperiodorlowestsystem)
    {
        $this->earliestperiodorlowestsystem = $earliestperiodorlowestsystem;

        return $this;
    }

    /**
     * Get earliestperiodorlowestsystem
     *
     * @return string
     */
    public function getEarliestperiodorlowestsystem()
    {
        return $this->earliestperiodorlowestsystem;
    }

    /**
     * Set formation
     *
     * @param string $formation
     *
     * @return Stratigraphy
     */
    public function setFormation($formation)
    {
        $this->formation = $formation;

        return $this;
    }

    /**
     * Get formation
     *
     * @return string
     */
    public function getFormation()
    {
        return $this->formation;
    }

    /**
     * Set group
     *
     * @param string $group
     *
     * @return Stratigraphy
     */
    public function setGroup($group)
    {
        $this->group_ = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group_;
    }

    public function getGroup_() {
        return $this->getGroup();
    }
    /**
     * Set highestbiostratigraphiczone
     *
     * @param string $highestbiostratigraphiczone
     *
     * @return Stratigraphy
     */
    public function setHighestbiostratigraphiczone($highestbiostratigraphiczone)
    {
        $this->highestbiostratigraphiczone = $highestbiostratigraphiczone;

        return $this;
    }

    /**
     * Get highestbiostratigraphiczone
     *
     * @return string
     */
    public function getHighestbiostratigraphiczone()
    {
        return $this->highestbiostratigraphiczone;
    }

    /**
     * Set latestageorhigheststage
     *
     * @param string $latestageorhigheststage
     *
     * @return Stratigraphy
     */
    public function setLatestageorhigheststage($latestageorhigheststage)
    {
        $this->latestageorhigheststage = $latestageorhigheststage;

        return $this;
    }

    /**
     * Get latestageorhigheststage
     *
     * @return string
     */
    public function getLatestageorhigheststage()
    {
        return $this->latestageorhigheststage;
    }

    /**
     * Set latesteonorhighesteonothem
     *
     * @param string $latesteonorhighesteonothem
     *
     * @return Stratigraphy
     */
    public function setLatesteonorhighesteonothem($latesteonorhighesteonothem)
    {
        $this->latesteonorhighesteonothem = $latesteonorhighesteonothem;

        return $this;
    }

    /**
     * Get latesteonorhighesteonothem
     *
     * @return string
     */
    public function getLatesteonorhighesteonothem()
    {
        return $this->latesteonorhighesteonothem;
    }

    /**
     * Set latestepochorhighestseries
     *
     * @param string $latestepochorhighestseries
     *
     * @return Stratigraphy
     */
    public function setLatestepochorhighestseries($latestepochorhighestseries)
    {
        $this->latestepochorhighestseries = $latestepochorhighestseries;

        return $this;
    }

    /**
     * Get latestepochorhighestseries
     *
     * @return string
     */
    public function getLatestepochorhighestseries()
    {
        return $this->latestepochorhighestseries;
    }

    /**
     * Set latesteraorhighesterathem
     *
     * @param string $latesteraorhighesterathem
     *
     * @return Stratigraphy
     */
    public function setLatesteraorhighesterathem($latesteraorhighesterathem)
    {
        $this->latesteraorhighesterathem = $latesteraorhighesterathem;

        return $this;
    }

    /**
     * Get latesteraorhighesterathem
     *
     * @return string
     */
    public function getLatesteraorhighesterathem()
    {
        return $this->latesteraorhighesterathem;
    }

    /**
     * Set latestperiodorhighestsystem
     *
     * @param string $latestperiodorhighestsystem
     *
     * @return Stratigraphy
     */
    public function setLatestperiodorhighestsystem($latestperiodorhighestsystem)
    {
        $this->latestperiodorhighestsystem = $latestperiodorhighestsystem;

        return $this;
    }

    /**
     * Get latestperiodorhighestsystem
     *
     * @return string
     */
    public function getLatestperiodorhighestsystem()
    {
        return $this->latestperiodorhighestsystem;
    }

    /**
     * Set lowestbiostratigraphiczone
     *
     * @param string $lowestbiostratigraphiczone
     *
     * @return Stratigraphy
     */
    public function setLowestbiostratigraphiczone($lowestbiostratigraphiczone)
    {
        $this->lowestbiostratigraphiczone = $lowestbiostratigraphiczone;

        return $this;
    }

    /**
     * Get lowestbiostratigraphiczone
     *
     * @return string
     */
    public function getLowestbiostratigraphiczone()
    {
        return $this->lowestbiostratigraphiczone;
    }

    /**
     * Set member
     *
     * @param string $member
     *
     * @return Stratigraphy
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @return Specimen
     */
    abstract public function getSpecimen();


    public function __toString()
    {
        return sprintf('%s %s %s', 
                $this->getEarliestepochorlowestseries(), 
                $this->getEarliestperiodorlowestsystem(),
                $this->getEarliestageorloweststage());
    }
    
    public function toArray()
    {
        return [
            'geologicalcontextid' => $this->getGeologicalcontextid(),
            'bed' => $this->getBed(),
            'earliestageorloweststage' => $this->getEarliestageorloweststage(),
            'earliesteonorlowesteonothem' => $this->getEarliesteonorlowesteonothem(),
            'earliestepochorlowestseries' => $this->getEarliestepochorlowestseries(),
            'earliesteraorlowesterathem' => $this->getEarliesteraorlowesterathem(),
            'earliestperiodorlowestsystem' => $this->getEarliestperiodorlowestsystem(),
            'formation' => $this->getFormation(),
            'group' => $this->getGroup(),
            'highestbiostratigraphiczone' => $this->getHighestbiostratigraphiczone(),
            'latestageorhigheststage' => $this->getLatestageorhigheststage(),
            'latesteonorhighesteonothem' => $this->getLatesteonorhighesteonothem(),
            'latestepochorhighestseries' => $this->getLatestepochorhighestseries(),
            'latesteraorhighesterathem' => $this->getLatesteraorhighesterathem(),
            'latestperiodorhighestsystem' => $this->getLatestperiodorhighestsystem(),
            'lowestbiostratigraphiczone' => $this->getLowestbiostratigraphiczone(),
            'member' => $this->getMember(),
        ];
    }
}
