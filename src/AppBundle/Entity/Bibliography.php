<?php

namespace AppBundle\Entity;

/**
 * Bibliography
 */
class Bibliography
{
    /**
     * @var guid
     */
    private $referenceid;

    /**
     * @var string
     */
    private $bibliographiccitation;

    /**
     * @var string
     */
    private $creator;

    /**
     * @var string
     */
    private $datePublication;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $rights;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $sourcefileid;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $taxonremarks;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \AppBundle\Entity\Specimen
     */
    private $occurrenceid;


    /**
     * Get referenceid
     *
     * @return guid
     */
    public function getReferenceid()
    {
        return strtoupper(bin2hex($this->referenceid));
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
     * Set occurrenceid
     *
     * @param \AppBundle\Entity\Specimen $occurrenceid
     *
     * @return Bibliography
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
}
