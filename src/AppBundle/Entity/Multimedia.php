<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\MultimediaRepository")
* @ORM\Table(name="Multimedia")
*/
class Multimedia
{
     /** 
     * @ORM\Id
     * @ORM\Column(type="integer") 
     */
    private $multimediaid;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $audience;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $contributor;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created ;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $creator;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $description;

    /** 
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $discriminator;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $format;

    /** 
     * @ORM\Column(type="string", length=300, nullable=false)
     */
    private $identifier;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $license;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $publisher ;

    /** 
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $references;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $rights;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $rightsholder;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $source;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sourcefileid;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $title;

    /** 
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $type;

   /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Specimen", mappedBy="multimedias")
     **/
    private $specimens;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->specimens = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get multimediaid
     *
     * @return guid
     */
    public function getMultimediaid()
    {
        return $this->multimediaid;
    }

    /**
     * Set audience
     *
     * @param string $audience
     *
     * @return Multimedia
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * Get audience
     *
     * @return string
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * Set contributor
     *
     * @param string $contributor
     *
     * @return Multimedia
     */
    public function setContributor($contributor)
    {
        $this->contributor = $contributor;

        return $this;
    }

    /**
     * Get contributor
     *
     * @return string
     */
    public function getContributor()
    {
        return $this->contributor;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Multimedia
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
     * Set creator
     *
     * @param string $creator
     *
     * @return Multimedia
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
     * Set description
     *
     * @param string $description
     *
     * @return Multimedia
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
     * Set discriminator
     *
     * @param string $discriminator
     *
     * @return Multimedia
     */
    public function setDiscriminator($discriminator)
    {
        $this->discriminator = $discriminator;

        return $this;
    }

    /**
     * Get discriminator
     *
     * @return string
     */
    public function getDiscriminator()
    {
        return $this->discriminator;
    }

    /**
     * Set format
     *
     * @param string $format
     *
     * @return Multimedia
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return Multimedia
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
     * Set license
     *
     * @param string $license
     *
     * @return Multimedia
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * Get license
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Multimedia
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
     * Set publisher
     *
     * @param string $publisher
     *
     * @return Multimedia
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set references
     *
     * @param string $references
     *
     * @return Multimedia
     */
    public function setReferences($references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * Get references
     *
     * @return string
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set rights
     *
     * @param string $rights
     *
     * @return Multimedia
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
     * @return Multimedia
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
     * Set source
     *
     * @param string $source
     *
     * @return Multimedia
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
     * @return Multimedia
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
     * Set title
     *
     * @param string $title
     *
     * @return Multimedia
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
     * @return Multimedia
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
     * Add occurrenceid
     *
     * @param \AppBundle\Entity\Specimen $occurrenceid
     *
     * @return Multimedia
     */
    public function addOccurrenceid(\AppBundle\Entity\Specimen $occurrenceid)
    {
        $this->occurrenceid[] = $occurrenceid;

        return $this;
    }

    /**
     * Remove occurrenceid
     *
     * @param \AppBundle\Entity\Specimen $occurrenceid
     */
    public function removeOccurrenceid(\AppBundle\Entity\Specimen $occurrenceid)
    {
        $this->occurrenceid->removeElement($occurrenceid);
    }

    /**
     * Get occurrenceid
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOccurrenceid()
    {
        return $this->occurrenceid;
    }
    
    public function __toString() {
        return $this->getIdentifier() ;
    }
    
    public function toArray()
    {
        return [
            'occurrenceid'=> null,
            'multimediaid' => $this->getMultimediaid(),
            'audience' => $this->getAudience(),
            'contributor' => $this->getContributor(),
            'created' => $this->getCreated(),
            'creator' => $this->getCreator(),
            'description' => $this->getDescription(),
            'discriminator' => $this->getDiscriminator(),
            'format' => $this->getFormat(),
            'identifier' => $this->getIdentifier(),
            'license' => $this->getLicense(),
            'modified' => $this->getModified(),
            'publisher' => $this->getPublisher(),
            'references' => $this->getReferences(),
            'rights' => $this->getRights(),
            'rightsholder' => $this->getRightsholder(),
            'source' => $this->getSource(),
            'title' => $this->getTitle(),
            'type' => $this->getType(),
        ];
    }
}
