<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\TaxonRepository")
* @ORM\Table(name="Taxons")
*/
class Taxon
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="rawid") 
     */
    private $taxonid;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $acceptednameusage;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true, name="class_")
     */
    private $class;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $dwcataxonid;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $family;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $genus;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $higherclassification;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $infraspecificepithet;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $kingdom;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nameaccordingto;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $namepublishedin;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $namepublishedinyear;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $nomenclaturalcode;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $nomenclaturalstatus;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true, name="order_")
     */
    private $order;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $originalnameusage;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $parentnameusage;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $phylum;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $scientificname;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $scientificnameauthorship;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sourcefileid;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $specificepithet;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $subgenus;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $taxonomicstatus;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $taxonrank;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $taxonremarks;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $verbatimtaxonrank;

    /** 
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $vernacularname;
    /**
     * @ORM\OneToOne(targetEntity="Determination", mappedBy="taxon")
     **/
    private $determination;

    /**
     * Get taxonid
     *
     * @return guid
     */
    public function getTaxonid()
    {
        return $this->taxonid;
    }

    /**
     * Set acceptednameusage
     *
     * @param string $acceptednameusage
     *
     * @return Taxon
     */
    public function setAcceptednameusage($acceptednameusage)
    {
        $this->acceptednameusage = $acceptednameusage;

        return $this;
    }

    /**
     * Get acceptednameusage
     *
     * @return string
     */
    public function getAcceptednameusage()
    {
        return $this->acceptednameusage;
    }

    /**
     * Set class
     *
     * @param string $class
     *
     * @return Taxon
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Taxon
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
     * Set dwcataxonid
     *
     * @param string $dwcataxonid
     *
     * @return Taxon
     */
    public function setDwcataxonid($dwcataxonid)
    {
        $this->dwcataxonid = $dwcataxonid;

        return $this;
    }

    /**
     * Get dwcataxonid
     *
     * @return string
     */
    public function getDwcataxonid()
    {
        return $this->dwcataxonid;
    }

    /**
     * Set family
     *
     * @param string $family
     *
     * @return Taxon
     */
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Get family
     *
     * @return string
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Set genus
     *
     * @param string $genus
     *
     * @return Taxon
     */
    public function setGenus($genus)
    {
        $this->genus = $genus;

        return $this;
    }

    /**
     * Get genus
     *
     * @return string
     */
    public function getGenus()
    {
        return $this->genus;
    }

    /**
     * Set higherclassification
     *
     * @param string $higherclassification
     *
     * @return Taxon
     */
    public function setHigherclassification($higherclassification)
    {
        $this->higherclassification = $higherclassification;

        return $this;
    }

    /**
     * Get higherclassification
     *
     * @return string
     */
    public function getHigherclassification()
    {
        return $this->higherclassification;
    }

    /**
     * Set infraspecificepithet
     *
     * @param string $infraspecificepithet
     *
     * @return Taxon
     */
    public function setInfraspecificepithet($infraspecificepithet)
    {
        $this->infraspecificepithet = $infraspecificepithet;

        return $this;
    }

    /**
     * Get infraspecificepithet
     *
     * @return string
     */
    public function getInfraspecificepithet()
    {
        return $this->infraspecificepithet;
    }

    /**
     * Set kingdom
     *
     * @param string $kingdom
     *
     * @return Taxon
     */
    public function setKingdom($kingdom)
    {
        $this->kingdom = $kingdom;

        return $this;
    }

    /**
     * Get kingdom
     *
     * @return string
     */
    public function getKingdom()
    {
        return $this->kingdom;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Taxon
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
     * Set nameaccordingto
     *
     * @param string $nameaccordingto
     *
     * @return Taxon
     */
    public function setNameaccordingto($nameaccordingto)
    {
        $this->nameaccordingto = $nameaccordingto;

        return $this;
    }

    /**
     * Get nameaccordingto
     *
     * @return string
     */
    public function getNameaccordingto()
    {
        return $this->nameaccordingto;
    }

    /**
     * Set namepublishedin
     *
     * @param string $namepublishedin
     *
     * @return Taxon
     */
    public function setNamepublishedin($namepublishedin)
    {
        $this->namepublishedin = $namepublishedin;

        return $this;
    }

    /**
     * Get namepublishedin
     *
     * @return string
     */
    public function getNamepublishedin()
    {
        return $this->namepublishedin;
    }

    /**
     * Set namepublishedinyear
     *
     * @param integer $namepublishedinyear
     *
     * @return Taxon
     */
    public function setNamepublishedinyear($namepublishedinyear)
    {
        $this->namepublishedinyear = $namepublishedinyear;

        return $this;
    }

    /**
     * Get namepublishedinyear
     *
     * @return integer
     */
    public function getNamepublishedinyear()
    {
        return $this->namepublishedinyear;
    }

    /**
     * Set nomenclaturalcode
     *
     * @param string $nomenclaturalcode
     *
     * @return Taxon
     */
    public function setNomenclaturalcode($nomenclaturalcode)
    {
        $this->nomenclaturalcode = $nomenclaturalcode;

        return $this;
    }

    /**
     * Get nomenclaturalcode
     *
     * @return string
     */
    public function getNomenclaturalcode()
    {
        return $this->nomenclaturalcode;
    }

    /**
     * Set nomenclaturalstatus
     *
     * @param string $nomenclaturalstatus
     *
     * @return Taxon
     */
    public function setNomenclaturalstatus($nomenclaturalstatus)
    {
        $this->nomenclaturalstatus = $nomenclaturalstatus;

        return $this;
    }

    /**
     * Get nomenclaturalstatus
     *
     * @return string
     */
    public function getNomenclaturalstatus()
    {
        return $this->nomenclaturalstatus;
    }

    /**
     * Set order
     *
     * @param string $order
     *
     * @return Taxon
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set originalnameusage
     *
     * @param string $originalnameusage
     *
     * @return Taxon
     */
    public function setOriginalnameusage($originalnameusage)
    {
        $this->originalnameusage = $originalnameusage;

        return $this;
    }

    /**
     * Get originalnameusage
     *
     * @return string
     */
    public function getOriginalnameusage()
    {
        return $this->originalnameusage;
    }

    /**
     * Set parentnameusage
     *
     * @param string $parentnameusage
     *
     * @return Taxon
     */
    public function setParentnameusage($parentnameusage)
    {
        $this->parentnameusage = $parentnameusage;

        return $this;
    }

    /**
     * Get parentnameusage
     *
     * @return string
     */
    public function getParentnameusage()
    {
        return $this->parentnameusage;
    }

    /**
     * Set phylum
     *
     * @param string $phylum
     *
     * @return Taxon
     */
    public function setPhylum($phylum)
    {
        $this->phylum = $phylum;

        return $this;
    }

    /**
     * Get phylum
     *
     * @return string
     */
    public function getPhylum()
    {
        return $this->phylum;
    }

    /**
     * Set scientificname
     *
     * @param string $scientificname
     *
     * @return Taxon
     */
    public function setScientificname($scientificname)
    {
        $this->scientificname = $scientificname;

        return $this;
    }

    /**
     * Get scientificname
     *
     * @return string
     */
    public function getScientificname()
    {
        return $this->scientificname;
    }

    /**
     * Set scientificnameauthorship
     *
     * @param string $scientificnameauthorship
     *
     * @return Taxon
     */
    public function setScientificnameauthorship($scientificnameauthorship)
    {
        $this->scientificnameauthorship = $scientificnameauthorship;

        return $this;
    }

    /**
     * Get scientificnameauthorship
     *
     * @return string
     */
    public function getScientificnameauthorship()
    {
        return $this->scientificnameauthorship;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Taxon
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
     * Set specificepithet
     *
     * @param string $specificepithet
     *
     * @return Taxon
     */
    public function setSpecificepithet($specificepithet)
    {
        $this->specificepithet = $specificepithet;

        return $this;
    }

    /**
     * Get specificepithet
     *
     * @return string
     */
    public function getSpecificepithet()
    {
        return $this->specificepithet;
    }

    /**
     * Set subgenus
     *
     * @param string $subgenus
     *
     * @return Taxon
     */
    public function setSubgenus($subgenus)
    {
        $this->subgenus = $subgenus;

        return $this;
    }

    /**
     * Get subgenus
     *
     * @return string
     */
    public function getSubgenus()
    {
        return $this->subgenus;
    }

    /**
     * Set taxonomicstatus
     *
     * @param string $taxonomicstatus
     *
     * @return Taxon
     */
    public function setTaxonomicstatus($taxonomicstatus)
    {
        $this->taxonomicstatus = $taxonomicstatus;

        return $this;
    }

    /**
     * Get taxonomicstatus
     *
     * @return string
     */
    public function getTaxonomicstatus()
    {
        return $this->taxonomicstatus;
    }

    /**
     * Set taxonrank
     *
     * @param string $taxonrank
     *
     * @return Taxon
     */
    public function setTaxonrank($taxonrank)
    {
        $this->taxonrank = $taxonrank;

        return $this;
    }

    /**
     * Get taxonrank
     *
     * @return string
     */
    public function getTaxonrank()
    {
        return $this->taxonrank;
    }

    /**
     * Set taxonremarks
     *
     * @param string $taxonremarks
     *
     * @return Taxon
     */
    public function setTaxonremarks($taxonremarks)
    {
        $this->taxonremarks = $taxonremarks;

        return $this;
    }

    /**
     * Get taxonremarks
     *
     * @return string
     */
    public function getTaxonremarks()
    {
        return $this->taxonremarks;
    }

    /**
     * Set verbatimtaxonrank
     *
     * @param string $verbatimtaxonrank
     *
     * @return Taxon
     */
    public function setVerbatimtaxonrank($verbatimtaxonrank)
    {
        $this->verbatimtaxonrank = $verbatimtaxonrank;

        return $this;
    }

    /**
     * Get verbatimtaxonrank
     *
     * @return string
     */
    public function getVerbatimtaxonrank()
    {
        return $this->verbatimtaxonrank;
    }

    /**
     * Set vernacularname
     *
     * @param string $vernacularname
     *
     * @return Taxon
     */
    public function setVernacularname($vernacularname)
    {
        $this->vernacularname = $vernacularname;

        return $this;
    }

    /**
     * Get vernacularname
     *
     * @return string
     */
    public function getVernacularname()
    {
        return $this->vernacularname;
    }
    
    /**
     * Get determination
     *
     * @return \AppBundle\Entity\Determination
     */
    public function getDetermination()
    {
        return $this->determination;
    }
    
    public function __toString()
    {
        return trim(sprintf('%s %s', $this->scientificname, $this->scientificnameauthorship)); 
    }
    
    public function toArray() {
        return [
            'taxonid' => $this->getTaxonid(),
            'acceptednameusage' => $this->getAcceptednameusage(),
            'class' => $this->getClass(),
            'created' => $this->getCreated(),
            'family' => $this->getFamily(),
            'genus' => $this->getGenus(),
            'higherclassification' => $this->getHigherclassification(),
            'infraspecificepithet' => $this->getInfraspecificepithet(),
            'kingdom' => $this->getKingdom(),
            'modified' => $this->getModified(),
            'nameaccordingto' => $this->getNameaccordingto(),
            'namepublishedin' => $this->getNamepublishedin(),
            'namepublishedinyear' => $this->getNamepublishedinyear(),
            'nomenclaturalcode' => $this->getNomenclaturalcode(),
            'nomenclaturalstatus' => $this->getNomenclaturalstatus(),
            'order' => $this->getOrder(),
            'originalnameusage' => $this->getOriginalnameusage(),
            'parentnameusage' => $this->getParentnameusage(),
            'phylum' => $this->getPhylum(),
            'scientificname' => $this->getScientificname(),
            'scientificnameauthorship' => $this->getScientificnameauthorship(),
            'specificepithet' => $this->getSpecificepithet(),
            'subgenus' => $this->getSubgenus(),
            'taxonomicstatus' => $this->getTaxonomicstatus(),
            'taxonrank' => $this->getTaxonrank(),
            'taxonremarks' => $this->getTaxonremarks(),
            'verbatimtaxonrank' => $this->getVerbatimtaxonrank(),
            'vernacularname' => $this->getVernacularname(),
        ];
    }
}
