<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\BibliographyRepository")
* @ORM\Table(name="Bibliographies")
*/
class Bibliography
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="rawid") 
     */
    private $referenceid;

    /** 
     * @ORM\Column(type="string", length=1500, nullable=true)
     */
    private $bibliographiccitation;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $creator;

    /** 
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $datePublication;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $identifier;

    /** 
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $language;

    /** 
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $rights;

    /** 
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $source;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sourcefileid;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /** 
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $taxonremarks;

    /** 
     * @ORM\Column(type="string", length=600, nullable=true)
     */
    private $title;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Specimen", inversedBy="bibliographies", fetch="LAZY")
    * @ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")
    */
    protected $specimen;


    /**
     * Get referenceid
     *
     * @return guid
     */
    public function getReferenceid()
    {
        return$this->referenceid;
    }

    /**
     * Set bibliographiccitation
     *
     * @param string $bibliographiccitation
     *
     * @return Bibliography
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
     * Set creator
     *
     * @param string $creator
     *
     * @return Bibliography
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set datePublication
     *
     * @param string $datePublication
     *
     * @return Bibliography
     */
    public function setDatePublication($datePublication)
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    /**
     * Get datePublication
     *
     * @return string
     */
    public function getDatePublication()
    {
        return $this->datePublication;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Bibliography
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return Bibliography
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return Bibliography
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set rights
     *
     * @param string $rights
     *
     * @return Bibliography
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
     * Set source
     *
     * @param string $source
     *
     * @return Bibliography
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Bibliography
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
     * Set subject
     *
     * @param string $subject
     *
     * @return Bibliography
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set taxonremarks
     *
     * @param string $taxonremarks
     *
     * @return Bibliography
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
     * Set title
     *
     * @param string $title
     *
     * @return Bibliography
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Bibliography
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set specimen
     *
     * @param \AppBundle\Entity\Specimen $occurrence
     *
     * @return Bibliography
     */
    public function setSpecimen(\AppBundle\Entity\Specimen $occurrence = null)
    {
        $this->specimen = $occurrence;

        return $this;
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