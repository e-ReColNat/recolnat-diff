<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\RecolteRepository")
* @ORM\Table(name="Recoltes")
*/
class Recolte
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="rawid") 
     */
    private $eventid;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $decade;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eday;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $emonth;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $eventdate;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $eventremarks;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eyear;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $fieldnotes;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $fieldnumber;

    /** 
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $habitat;

    /** 
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    private $recordedby;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sday;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $smonth;

    /** 
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $sourcefileid;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $syear;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $verbatimeventdate;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Localisation", inversedBy="recoltes", fetch="LAZY")
    * @ORM\JoinColumn(name="locationid", referencedColumnName="locationid")
    */
    private $localisation;


    /**
     * Get eventid
     *
     * @return rawid
     */
    public function getEventid()
    {
        return $this->eventid;
    }

    /**
     * Set decade
     *
     * @param integer $decade
     *
     * @return Recolte
     */
    public function setDecade($decade)
    {
        $this->decade = $decade;

        return $this;
    }

    /**
     * Get decade
     *
     * @return integer
     */
    public function getDecade()
    {
        return $this->decade;
    }

    /**
     * Set eday
     *
     * @param integer $eday
     *
     * @return Recolte
     */
    public function setEday($eday)
    {
        $this->eday = $eday;

        return $this;
    }

    /**
     * Get eday
     *
     * @return integer
     */
    public function getEday()
    {
        return $this->eday;
    }

    /**
     * Set emonth
     *
     * @param integer $emonth
     *
     * @return Recolte
     */
    public function setEmonth($emonth)
    {
        $this->emonth = $emonth;

        return $this;
    }

    /**
     * Get emonth
     *
     * @return integer
     */
    public function getEmonth()
    {
        return $this->emonth;
    }

    /**
     * Set eventdate
     *
     * @param \DateTime $eventdate
     *
     * @return Recolte
     */
    public function setEventdate($eventdate)
    {
        $this->eventdate = $eventdate;

        return $this;
    }

    /**
     * Get eventdate
     *
     * @return \DateTime
     */
    public function getEventdate()
    {
        return $this->eventdate;
    }

    /**
     * Set eventremarks
     *
     * @param string $eventremarks
     *
     * @return Recolte
     */
    public function setEventremarks($eventremarks)
    {
        $this->eventremarks = $eventremarks;

        return $this;
    }

    /**
     * Get eventremarks
     *
     * @return string
     */
    public function getEventremarks()
    {
        return $this->eventremarks;
    }

    /**
     * Set eyear
     *
     * @param integer $eyear
     *
     * @return Recolte
     */
    public function setEyear($eyear)
    {
        $this->eyear = $eyear;

        return $this;
    }

    /**
     * Get eyear
     *
     * @return integer
     */
    public function getEyear()
    {
        return $this->eyear;
    }

    /**
     * Set fieldnotes
     *
     * @param string $fieldnotes
     *
     * @return Recolte
     */
    public function setFieldnotes($fieldnotes)
    {
        $this->fieldnotes = $fieldnotes;

        return $this;
    }

    /**
     * Get fieldnotes
     *
     * @return string
     */
    public function getFieldnotes()
    {
        return $this->fieldnotes;
    }

    /**
     * Set fieldnumber
     *
     * @param string $fieldnumber
     *
     * @return Recolte
     */
    public function setFieldnumber($fieldnumber)
    {
        $this->fieldnumber = $fieldnumber;

        return $this;
    }

    /**
     * Get fieldnumber
     *
     * @return string
     */
    public function getFieldnumber()
    {
        return $this->fieldnumber;
    }

    /**
     * Set habitat
     *
     * @param string $habitat
     *
     * @return Recolte
     */
    public function setHabitat($habitat)
    {
        $this->habitat = $habitat;

        return $this;
    }

    /**
     * Get habitat
     *
     * @return string
     */
    public function getHabitat()
    {
        return $this->habitat;
    }

    /**
     * Set recordedby
     *
     * @param string $recordedby
     *
     * @return Recolte
     */
    public function setRecordedby($recordedby)
    {
        $this->recordedby = $recordedby;

        return $this;
    }

    /**
     * Get recordedby
     *
     * @return string
     */
    public function getRecordedby()
    {
        return $this->recordedby;
    }

    /**
     * Set sday
     *
     * @param integer $sday
     *
     * @return Recolte
     */
    public function setSday($sday)
    {
        $this->sday = $sday;

        return $this;
    }

    /**
     * Get sday
     *
     * @return integer
     */
    public function getSday()
    {
        return $this->sday;
    }

    /**
     * Set smonth
     *
     * @param integer $smonth
     *
     * @return Recolte
     */
    public function setSmonth($smonth)
    {
        $this->smonth = $smonth;

        return $this;
    }

    /**
     * Get smonth
     *
     * @return integer
     */
    public function getSmonth()
    {
        return $this->smonth;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Recolte
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

    /**
     * Set syear
     *
     * @param integer $syear
     *
     * @return Recolte
     */
    public function setSyear($syear)
    {
        $this->syear = $syear;

        return $this;
    }

    /**
     * Get syear
     *
     * @return integer
     */
    public function getSyear()
    {
        return $this->syear;
    }

    /**
     * Set verbatimeventdate
     *
     * @param \DateTime $verbatimeventdate
     *
     * @return Recolte
     */
    public function setVerbatimeventdate($verbatimeventdate)
    {
        $this->verbatimeventdate = $verbatimeventdate;

        return $this;
    }

    /**
     * Get verbatimeventdate
     *
     * @return \DateTime
     */
    public function getVerbatimeventdate()
    {
        return $this->verbatimeventdate;
    }

    /**
     * Set locationid
     *
     * @param \AppBundle\Entity\Localisation $locationid
     *
     * @return Recolte
     */
    public function setLocation(\AppBundle\Entity\Localisation $locationid = null)
    {
        $this->locationid = $locationid;

        return $this;
    }

    /**
     * Get locationid
     *
     * @return \AppBundle\Entity\Localisation
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }

}