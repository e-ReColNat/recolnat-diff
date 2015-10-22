<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\DeterminationRepository")
* @ORM\Table(name="Determinations")
*/
class Determination
{
    /**
    * @ORM\Id
    * @ORM\Column(type="rawid", length=16, name="identificationid", nullable=false)
    */
    private $identificationid;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateidentified;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $dwcaidentificationid;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $identificationqualifier;

    /** 
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $identificationreferences;

    /** 
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $identificationremarks;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $identificationverifstatus;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $identifiedby;

     /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /** 
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $sourcefileid;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $typestatus;

    /**
    * @var \AppBundle\Entity\Specimen
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Specimen", inversedBy="determinations", fetch="LAZY")
    * @ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")
    */
    protected $specimen;

    /**
     * @ORM\OneToOne(targetEntity="Taxon", inversedBy="determination")
     * @ORM\JoinColumn(name="taxonid", referencedColumnName="taxonid")
     **/
    private $taxon;


    /**
     * Get identificationid
     *
     * @return guid
     */
    public function getIdentificationid()
    {
        //return strtoupper(bin2hex($this->identificationid));
        return $this->identificationid;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Determination
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set dateidentified
     *
     * @param \DateTime $dateidentified
     *
     * @return Determination
     */
    public function setDateidentified($dateidentified)
    {
        $this->dateidentified = $dateidentified;

        return $this;
    }

    /**
     * Get dateidentified
     *
     * @return \DateTime
     */
    public function getDateidentified()
    {
        return $this->dateidentified;
    }

    /**
     * Set dwcaidentificationid
     *
     * @param string $dwcaidentificationid
     *
     * @return Determination
     */
    public function setDwcaidentificationid($dwcaidentificationid)
    {
        $this->dwcaidentificationid = $dwcaidentificationid;

        return $this;
    }

    /**
     * Get dwcaidentificationid
     *
     * @return string
     */
    public function getDwcaidentificationid()
    {
        return $this->dwcaidentificationid;
    }

    /**
     * Set identificationqualifier
     *
     * @param string $identificationqualifier
     *
     * @return Determination
     */
    public function setIdentificationqualifier($identificationqualifier)
    {
        $this->identificationqualifier = $identificationqualifier;

        return $this;
    }

    /**
     * Get identificationqualifier
     *
     * @return string
     */
    public function getIdentificationqualifier()
    {
        return $this->identificationqualifier;
    }

    /**
     * Set identificationreferences
     *
     * @param string $identificationreferences
     *
     * @return Determination
     */
    public function setIdentificationreferences($identificationreferences)
    {
        $this->identificationreferences = $identificationreferences;

        return $this;
    }

    /**
     * Get identificationreferences
     *
     * @return string
     */
    public function getIdentificationreferences()
    {
        return $this->identificationreferences;
    }

    /**
     * Set identificationremarks
     *
     * @param string $identificationremarks
     *
     * @return Determination
     */
    public function setIdentificationremarks($identificationremarks)
    {
        $this->identificationremarks = $identificationremarks;

        return $this;
    }

    /**
     * Get identificationremarks
     *
     * @return string
     */
    public function getIdentificationremarks()
    {
        return $this->identificationremarks;
    }

    /**
     * Set identificationverifstatus
     *
     * @param integer $identificationverifstatus
     *
     * @return Determination
     */
    public function setIdentificationverifstatus($identificationverifstatus)
    {
        $this->identificationverifstatus = $identificationverifstatus;

        return $this;
    }

    /**
     * Get identificationverifstatus
     *
     * @return integer
     */
    public function getIdentificationverifstatus()
    {
        return $this->identificationverifstatus;
    }

    /**
     * Set identifiedby
     *
     * @param string $identifiedby
     *
     * @return Determination
     */
    public function setIdentifiedby($identifiedby)
    {
        $this->identifiedby = $identifiedby;

        return $this;
    }

    /**
     * Get identifiedby
     *
     * @return string
     */
    public function getIdentifiedby()
    {
        return $this->identifiedby;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Determination
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Determination
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
     * Set typestatus
     *
     * @param string $typestatus
     *
     * @return Determination
     */
    public function setTypestatus($typestatus)
    {
        $this->typestatus = $typestatus;

        return $this;
    }

    /**
     * Get typestatus
     *
     * @return string
     */
    public function getTypestatus()
    {
        return $this->typestatus;
    }

    /**
     * Set occurrenceid
     *
     * @param \AppBundle\Entity\Specimen $occurrenceid
     *
     * @return Determination
     */
    public function setOccurrenceid(\AppBundle\Entity\Specimen $occurrenceid = null)
    {
        $this->occurrenceid = $occurrenceid;

        return $this;
    }

    /**
     * Get occurrenceid
     *
     * @return \AppBundle\Entity\Specimen
     */
    public function getOccurrenceid()
    {
        return $this->occurrenceid;
    }

    /**
     * Set taxonid
     *
     * @param \AppBundle\Entity\Taxon $taxon
     *
     * @return Determination
     */
    public function setTaxon(\AppBundle\Entity\Taxon $taxon = null)
    {
        $this->taxon = $taxon;

        return $this;
    }

    /**
     * Get taxonid
     *
     * @return \AppBundle\Entity\Taxon
     */
    public function getTaxon()
    {
        return $this->taxon;
    }
    
    /**
     * Get specimen
     *
     * @return \AppBundle\Entity\Specimen
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

}
