<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\StratigraphyRepository")
* @ORM\Table(name="Stratigraphies")
*/
class Stratigraphy
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=10) 
     */
    private $geologicalcontextid;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $bed;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $earliestageorloweststage;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $earliesteonorlowesteonothem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $earliestepochorlowestseries;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $earliesteraorlowesterathem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $earliestperiodorlowestsystem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $formation;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $group_;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $highestbiostratigraphiczone;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $latestageorhigheststage;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $latesteonorhighesteonothem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $latestepochorhighestseries;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $latesteraorhighesterathem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $latestperiodorhighestsystem;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $lowestbiostratigraphiczone;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $member;

    /** 
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $sourcefileid;
    
     /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Specimen", mappedBy="stratigraphy")
     **/
    private $specimen;

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
        $this->group = $group;

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
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Stratigraphy
     */
    public function setSourcefileid($sourcefileid)
    {
        $this->sourcefileid = $sourcefileid;

        return $this;
    }

    /**
     * Get sourcefileid
     *
     * @return string
     */
    public function getSourcefileid()
    {
        return $this->sourcefileid;
    }

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
