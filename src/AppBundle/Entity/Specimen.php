<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\SpecimenRepository")
* @ORM\Table(name="Specimens")
*/
class Specimen
{
    
    /** 
     * @ORM\Id
     * @ORM\Column(type="rawid") 
     */
    private $occurrenceid;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $accessrights;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $associatedmedia;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $associatedreferences;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $associatedtaxa;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $basisofrecord;

    /** 
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    private $bibliographiccitation;

    /** 
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $catalognumber;

    /** 
     * @ORM\Column(type="string", length=60, nullable=false)
     */
    private $collectioncode;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $disposition;

    /** 
     * @ORM\Column(type="string", length=155, nullable=false)
     */
    private $dwcaid;

    /** 
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hascoordinates = '0';

    /** 
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasmedia = '0';

    /** 
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $institutioncode;

    /** 
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $lifestage;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $occurrenceremarks;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $ownerinstitutioncode;

   /** 
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $recordnumber;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $rights;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $rightsholder;

    /** 
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $sex;

    /** 
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $sourcefileid;

    /**
     * @ORM\OneToOne(targetEntity="Collection", inversedBy="specimen", fetch="EAGER")
     * @ORM\JoinColumn(name="collectionid", referencedColumnName="collectionid")
     **/
    private $collection;

    /**
     * @ORM\OneToOne(targetEntity="Stratigraphy", fetch="EAGER")
     * @ORM\JoinColumn(name="geologicalcontextid", referencedColumnName="geologicalcontextid")
     **/
    private $stratigraphy;

    /**
     * @ORM\OneToOne(targetEntity="Recolte", fetch="EAGER")
     * @ORM\JoinColumn(name="eventid", referencedColumnName="eventid")
     **/
    private $recolte;

    /**
     * @ORM\ManyToMany(targetEntity="Multimedia", inversedBy="specimens")
     * @ORM\JoinTable(name="MultimediaHasOccurrences",
     *      joinColumns={@ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="multimediaid", referencedColumnName="multimediaid")}
     *      )
     **/
    private $multimedias;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Determination", mappedBy="specimen", fetch="EAGER")
    * @ORM\OrderBy({"identificationverifstatus" = "DESC", "dateidentified" = "DESC"})
    */
    protected $determinations;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Bibliography", mappedBy="specimen", fetch="EAGER")
    */
    protected $bibliographies;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->multimedias = new \Doctrine\Common\Collections\ArrayCollection();
        $this->determinations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bibliographies = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get occurrenceid
     *
     * @return guid
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
     * @param \AppBundle\Entity\Collection $collection
     *
     * @return Specimen
     */
    public function setCollection(\AppBundle\Entity\Collection $collection = null)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collectionid
     *
     * @return \AppBundle\Entity\Collection
     */
    public function getCollection()
    {
        return $this->collection;
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
        $this->geologicalcontext = $geologicalcontextid;

        return $this;
    }

    /**
     * Get stratigraphy
     *
     * @return \AppBundle\Entity\Stratigraphy
     */
    public function getStratigraphy()
    {
        return $this->stratigraphy;
    }

    /**
     * Set eventid
     *
     * @param \AppBundle\Entity\Recolte $eventid
     *
     * @return Specimen
     */
    public function setRecolte(\AppBundle\Entity\Recolte $eventid = null)
    {
        $this->eventid = $eventid;

        return $this;
    }

    /**
     * Get recolte
     *
     * @return \AppBundle\Entity\Recolte
     */
    public function getRecolte()
    {
        return $this->recolte;
    }

    /**
     * Add multimediaid
     *
     * @param \AppBundle\Entity\Multimedia $multimedia
     *
     * @return Specimen
     */
    public function addMultimediaid(\AppBundle\Entity\Multimedia $multimedia)
    {
        $this->multimedias[] = $multimedia;

        return $this;
    }

    /**
     * Remove multimedia
     *
     * @param \AppBundle\Entity\Multimedia $multimedia
     */
    public function removeMultimedia(\AppBundle\Entity\Multimedia $multimedia)
    {
        $this->multimedias->removeElement($multimedia);
    }

    /**
     * Get multimedias
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMultimedias()
    {
        return $this->multimedias;
    }
    /**
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDeterminations()
    {
        return $this->determinations;
    }
    
    /**
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBibliographies()
    {
        return $this->bibliographies;
    }
    
    public function getSpecimenId()
    {
        return $this->getInstitutioncode().$this->getCollectioncode().$this->getCatalognumber() ;
    }
}
