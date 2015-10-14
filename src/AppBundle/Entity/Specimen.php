<?php

namespace AppBundle\Entity;

/**
 * Specimen
 */
class Specimen
{
    /**
     * @var guid
     */
    private $occurrenceid;

    /**
     * @var string
     */
    private $accessrights;

    /**
     * @var string
     */
    private $associatedmedia;

    /**
     * @var string
     */
    private $associatedreferences;

    /**
     * @var string
     */
    private $associatedtaxa;

    /**
     * @var string
     */
    private $basisofrecord;

    /**
     * @var string
     */
    private $bibliographiccitation;

    /**
     * @var string
     */
    private $catalognumber;

    /**
     * @var string
     */
    private $collectioncode;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $disposition;

    /**
     * @var string
     */
    private $dwcaid;

    /**
     * @var boolean
     */
    private $hascoordinates = '0';

    /**
     * @var boolean
     */
    private $hasmedia = '0';

    /**
     * @var string
     */
    private $institutioncode;

    /**
     * @var string
     */
    private $lifestage;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var string
     */
    private $occurrenceremarks;

    /**
     * @var string
     */
    private $ownerinstitutioncode;

    /**
     * @var string
     */
    private $recordnumber;

    /**
     * @var string
     */
    private $rights;

    /**
     * @var string
     */
    private $rightsholder;

    /**
     * @var string
     */
    private $sex;

    /**
     * @var string
     */
    private $sourcefileid;

    /**
     * @var \AppBundle\Entity\Collection
     */
    private $collectionid;

    /**
     * @var \AppBundle\Entity\Stratigraphy
     */
    private $geologicalcontextid;

    /**
     * @var \AppBundle\Entity\Recolte
     */
    private $eventid;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $multimediaid;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->multimediaid = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get occurrenceid
     *
     * @return guid
     */
    public function getOccurrenceid()
    {
        return strtoupper(bin2hex($this->occurrenceid));
    }

    /**
     * Set accessrights
     *
     * @param string $accessrights
     *
     * @return Specimen
     */
    public function setAccessrights($accessrights)
    {
        $this->accessrights = $accessrights;

        return $this;
    }

    /**
     * Get accessrights
     *
     * @return string
     */
    public function getAccessrights()
    {
        return $this->accessrights;
    }

    /**
     * Set associatedmedia
     *
     * @param string $associatedmedia
     *
     * @return Specimen
     */
    public function setAssociatedmedia($associatedmedia)
    {
        $this->associatedmedia = $associatedmedia;

        return $this;
    }

    /**
     * Get associatedmedia
     *
     * @return string
     */
    public function getAssociatedmedia()
    {
        return $this->associatedmedia;
    }

    /**
     * Set associatedreferences
     *
     * @param string $associatedreferences
     *
     * @return Specimen
     */
    public function setAssociatedreferences($associatedreferences)
    {
        $this->associatedreferences = $associatedreferences;

        return $this;
    }

    /**
     * Get associatedreferences
     *
     * @return string
     */
    public function getAssociatedreferences()
    {
        return $this->associatedreferences;
    }

    /**
     * Set associatedtaxa
     *
     * @param string $associatedtaxa
     *
     * @return Specimen
     */
    public function setAssociatedtaxa($associatedtaxa)
    {
        $this->associatedtaxa = $associatedtaxa;

        return $this;
    }

    /**
     * Get associatedtaxa
     *
     * @return string
     */
    public function getAssociatedtaxa()
    {
        return $this->associatedtaxa;
    }

    /**
     * Set basisofrecord
     *
     * @param string $basisofrecord
     *
     * @return Specimen
     */
    public function setBasisofrecord($basisofrecord)
    {
        $this->basisofrecord = $basisofrecord;

        return $this;
    }

    /**
     * Get basisofrecord
     *
     * @return string
     */
    public function getBasisofrecord()
    {
        return $this->basisofrecord;
    }

    /**
     * Set bibliographiccitation
     *
     * @param string $bibliographiccitation
     *
     * @return Specimen
     */
    public function setBibliographiccitation($bibliographiccitation)
    {
        $this->bibliographiccitation = $bibliographiccitation;

        return $this;
    }

    /**
     * Get bibliographiccitation
     *
     * @return string
     */
    public function getBibliographiccitation()
    {
        return $this->bibliographiccitation;
    }

    /**
     * Set catalognumber
     *
     * @param string $catalognumber
     *
     * @return Specimen
     */
    public function setCatalognumber($catalognumber)
    {
        $this->catalognumber = $catalognumber;

        return $this;
    }

    /**
     * Get catalognumber
     *
     * @return string
     */
    public function getCatalognumber()
    {
        return $this->catalognumber;
    }

    /**
     * Set collectioncode
     *
     * @param string $collectioncode
     *
     * @return Specimen
     */
    public function setCollectioncode($collectioncode)
    {
        $this->collectioncode = $collectioncode;

        return $this;
    }

    /**
     * Get collectioncode
     *
     * @return string
     */
    public function getCollectioncode()
    {
        return $this->collectioncode;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Specimen
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
     * Set disposition
     *
     * @param string $disposition
     *
     * @return Specimen
     */
    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;

        return $this;
    }

    /**
     * Get disposition
     *
     * @return string
     */
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * Set dwcaid
     *
     * @param string $dwcaid
     *
     * @return Specimen
     */
    public function setDwcaid($dwcaid)
    {
        $this->dwcaid = $dwcaid;

        return $this;
    }

    /**
     * Get dwcaid
     *
     * @return string
     */
    public function getDwcaid()
    {
        return $this->dwcaid;
    }

    /**
     * Set hascoordinates
     *
     * @param boolean $hascoordinates
     *
     * @return Specimen
     */
    public function setHascoordinates($hascoordinates)
    {
        $this->hascoordinates = $hascoordinates;

        return $this;
    }

    /**
     * Get hascoordinates
     *
     * @return boolean
     */
    public function getHascoordinates()
    {
        return $this->hascoordinates;
    }

    /**
     * Set hasmedia
     *
     * @param boolean $hasmedia
     *
     * @return Specimen
     */
    public function setHasmedia($hasmedia)
    {
        $this->hasmedia = $hasmedia;

        return $this;
    }

    /**
     * Get hasmedia
     *
     * @return boolean
     */
    public function getHasmedia()
    {
        return $this->hasmedia;
    }

    /**
     * Set institutioncode
     *
     * @param string $institutioncode
     *
     * @return Specimen
     */
    public function setInstitutioncode($institutioncode)
    {
        $this->institutioncode = $institutioncode;

        return $this;
    }

    /**
     * Get institutioncode
     *
     * @return string
     */
    public function getInstitutioncode()
    {
        return $this->institutioncode;
    }

    /**
     * Set lifestage
     *
     * @param string $lifestage
     *
     * @return Specimen
     */
    public function setLifestage($lifestage)
    {
        $this->lifestage = $lifestage;

        return $this;
    }

    /**
     * Get lifestage
     *
     * @return string
     */
    public function getLifestage()
    {
        return $this->lifestage;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Specimen
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
     * Set occurrenceremarks
     *
     * @param string $occurrenceremarks
     *
     * @return Specimen
     */
    public function setOccurrenceremarks($occurrenceremarks)
    {
        $this->occurrenceremarks = $occurrenceremarks;

        return $this;
    }

    /**
     * Get occurrenceremarks
     *
     * @return string
     */
    public function getOccurrenceremarks()
    {
        return $this->occurrenceremarks;
    }

    /**
     * Set ownerinstitutioncode
     *
     * @param string $ownerinstitutioncode
     *
     * @return Specimen
     */
    public function setOwnerinstitutioncode($ownerinstitutioncode)
    {
        $this->ownerinstitutioncode = $ownerinstitutioncode;

        return $this;
    }

    /**
     * Get ownerinstitutioncode
     *
     * @return string
     */
    public function getOwnerinstitutioncode()
    {
        return $this->ownerinstitutioncode;
    }

    /**
     * Set recordnumber
     *
     * @param string $recordnumber
     *
     * @return Specimen
     */
    public function setRecordnumber($recordnumber)
    {
        $this->recordnumber = $recordnumber;

        return $this;
    }

    /**
     * Get recordnumber
     *
     * @return string
     */
    public function getRecordnumber()
    {
        return $this->recordnumber;
    }

    /**
     * Set rights
     *
     * @param string $rights
     *
     * @return Specimen
     */
    public function setRights($rights)
    {
        $this->rights = $rights;

        return $this;
    }

    /**
     * Get rights
     *
     * @return string
     */
    public function getRights()
    {
        return $this->rights;
    }

    /**
     * Set rightsholder
     *
     * @param string $rightsholder
     *
     * @return Specimen
     */
    public function setRightsholder($rightsholder)
    {
        $this->rightsholder = $rightsholder;

        return $this;
    }

    /**
     * Get rightsholder
     *
     * @return string
     */
    public function getRightsholder()
    {
        return $this->rightsholder;
    }

    /**
     * Set sex
     *
     * @param string $sex
     *
     * @return Specimen
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Specimen
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
     * Set collectionid
     *
     * @param \AppBundle\Entity\Collection $collectionid
     *
     * @return Specimen
     */
    public function setCollectionid(\AppBundle\Entity\Collection $collectionid = null)
    {
        $this->collectionid = $collectionid;

        return $this;
    }

    /**
     * Get collectionid
     *
     * @return \AppBundle\Entity\Collection
     */
    public function getCollectionid()
    {
        return $this->collectionid;
    }

    /**
     * Set geologicalcontextid
     *
     * @param \AppBundle\Entity\Stratigraphy $geologicalcontextid
     *
     * @return Specimen
     */
    public function setGeologicalcontextid(\AppBundle\Entity\Stratigraphy $geologicalcontextid = null)
    {
        $this->geologicalcontextid = $geologicalcontextid;

        return $this;
    }

    /**
     * Get geologicalcontextid
     *
     * @return \AppBundle\Entity\Stratigraphy
     */
    public function getGeologicalcontextid()
    {
        return $this->geologicalcontextid;
    }

    /**
     * Set eventid
     *
     * @param \AppBundle\Entity\Recolte $eventid
     *
     * @return Specimen
     */
    public function setEventid(\AppBundle\Entity\Recolte $eventid = null)
    {
        $this->eventid = $eventid;

        return $this;
    }

    /**
     * Get eventid
     *
     * @return \AppBundle\Entity\Recolte
     */
    public function getEventid()
    {
        return $this->eventid;
    }

    /**
     * Add multimediaid
     *
     * @param \AppBundle\Entity\Multimedia $multimediaid
     *
     * @return Specimen
     */
    public function addMultimediaid(\AppBundle\Entity\Multimedia $multimediaid)
    {
        $this->multimediaid[] = $multimediaid;

        return $this;
    }

    /**
     * Remove multimediaid
     *
     * @param \AppBundle\Entity\Multimedia $multimediaid
     */
    public function removeMultimediaid(\AppBundle\Entity\Multimedia $multimediaid)
    {
        $this->multimediaid->removeElement($multimediaid);
    }

    /**
     * Get multimediaid
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMultimediaid()
    {
        return $this->multimediaid;
    }
}
