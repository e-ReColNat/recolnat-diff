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
    protected $referenceid;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     */
    protected $bibliographiccitation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $creator;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $datePublication;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $identifier;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    protected $language;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $rights;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $source;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $subject;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    protected $taxonremarks;

    /**
     * @ORM\Column(type="string", length=600, nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Specimen", inversedBy="bibliographies", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")
     */
    protected $specimen;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;


    /**
     * Get referenceid
     *
     * @return string
     */
    public function getReferenceid()
    {
        return $this->referenceid;
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Bibliography
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
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Bibliography
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
     * Get specimen
     *
     * @return \AppBundle\Entity\Specimen
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    public function __toString()
    {
        return sprintf('%s %s', $this->getSubject(), $this->getCreator());
    }

    public function toArray()
    {
        $specimen = $this->getSpecimen();

        return [
            'occurrenceid' => !is_null($specimen) ? $specimen->getOccurrenceid() : null,
            'referenceid' => $this->getReferenceid(),
            'bibliographiccitation' => $this->getBibliographiccitation(),
            'creator' => $this->getCreator(),
            'date_publication' => $this->getDatePublication(),
            'description' => $this->getDescription(),
            'identifier' => $this->getIdentifier(),
            'language' => $this->getLanguage(),
            'rights' => $this->getRights(),
            'source' => $this->getSource(),
            'subject' => $this->getSubject(),
            'taxonremarks' => $this->getTaxonremarks(),
            'title' => $this->getTitle(),
            'type' => $this->getType()
        ];
    }
}
