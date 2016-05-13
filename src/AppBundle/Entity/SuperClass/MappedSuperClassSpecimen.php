<?php

namespace AppBundle\Entity\SuperClass;

use AppBundle\Entity\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\SuperClass\MappedSuperClassSpecimen as Specimen;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="Specimens")
 */
abstract class MappedSuperClassSpecimen
{

    /**
     * @ORM\Id
     * @ORM\Column(type="rawid")
     */
    protected $occurrenceid;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $accessrights;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $associatedmedia;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $associatedreferences;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $associatedtaxa;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $basisofrecord;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    protected $bibliographiccitation;

    /**
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    protected $catalognumber;

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     */
    protected $collectioncode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $disposition;

    /**
     * @ORM\Column(type="string", length=155, nullable=false)
     */
    protected $dwcaid;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $hascoordinates = 0;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $hasmedia = 0;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected $institutioncode;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $lifestage;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $modified;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $occurrenceremarks;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $ownerinstitutioncode;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $recordnumber;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $rights;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $rightsholder;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $sex;

    /**
     * Get occurrenceid
     *
     * @return string
     */
    public function getOccurrenceid()
    {
        return $this->occurrenceid;
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
     * @param integer $hascoordinates
     *
     * @return Specimen
     */
    public function setHascoordinates($hascoordinates)
    {
        $this->hascoordinates = $hascoordinates > 0 ? 1 : 0;

        return $this;
    }

    /**
     * Get hascoordinates
     *
     * @return integer
     */
    public function getHascoordinates()
    {
        return $this->hascoordinates;
    }

    /**
     * Set hasmedia
     *
     * @param integer $hasmedia
     *
     * @return Specimen
     */
    public function setHasmedia($hasmedia)
    {
        $this->hasmedia = $hasmedia > 0 ? 1 : 0;

        return $this;
    }

    /**
     * Get hasmedia
     *
     * @return boolean
     */
    public function getHasmedia()
    {
        return (bool) $this->hasmedia;
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
     * @return string
     */
    public function getSpecimenCode()
    {
        return $this->getInstitutioncode().$this->getCollectioncode().$this->getCatalognumber();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->catalognumber;
    }

    /**
     * @param string $specimenCode
     * @return string
     */
    public static function explodeSpecimenCode($specimenCode)
    {
        $explodeData = explode('#', $specimenCode);

        return end($explodeData);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'occurrenceid' => $this->getOccurrenceid(),
            'accessrights' => $this->getAccessrights(),
            'associatedmedia' => $this->getAssociatedmedia(),
            'associatedreferences' => $this->getAssociatedreferences(),
            'associatedtaxa' => $this->getAssociatedtaxa(),
            'basisofrecord' => $this->getBasisofrecord(),
            'bibliographiccitation' => $this->getBibliographiccitation(),
            'catalognumber' => $this->getCatalognumber(),
            'collectioncode' => $this->getCollectioncode(),
            'created' => $this->getCreated(),
            'disposition' => $this->getDisposition(),
            'institutioncode' => $this->getInstitutioncode(),
            'lifestage' => $this->getLifestage(),
            'modified' => $this->getModified(),
            'occurrenceremarks' => $this->getOccurrenceremarks(),
            'ownerinstitutioncode' => $this->getOwnerinstitutioncode(),
            'recordnumber' => $this->getRecordnumber(),
            'rights' => $this->getRights(),
            'rightsholder' => $this->getRightsholder(),
            'sex' => $this->getSex(),
            'geologicalcontextid' => !is_null($this->getStratigraphy()) ? $this->getStratigraphy()->getGeologicalcontextid() : null,
            'eventid' => !is_null($this->getRecolte()) ? $this->getRecolte()->getEventid() : null,
        ];
    }

    /**
     * Get collectionid
     *
     * @return \AppBundle\Entity\Collection
     */
    abstract public function getCollection();

    abstract public function getStratigraphy();

    abstract public function getRecolte();

    abstract public function getMultimedias();

    abstract public function getDeterminations();

    abstract public function getBibliographies();
}
