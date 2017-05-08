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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $identificationverifstatus;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $identifiedby;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $typestatus;

    /**
     * @var \AppBundle\Entity\Specimen
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Specimen", inversedBy="determinations", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")
     */
    protected $specimen;

    /**
     * @ORM\OneToOne(targetEntity="Taxon", inversedBy="determination", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="taxonid", referencedColumnName="taxonid")
     **/
    private $taxon;

    /**
     * @ORM\Column(type="rawid")
     */
    private $occurrenceid;

    /**
     * Get identificationid
     *
     * @return string
     */
    public function getIdentificationid()
    {
        return strtoupper($this->identificationid);
    }

    /**
     * Renvoie la clé discriminative entre deux enregistrements de bases de comparaison
     * @return string
     */
    public function getDiscriminationId()
    {
        if (null !== $this->getDateidentified()) {
            return $this->getDateidentified()->getTimestamp() . '#' . mb_strtolower($this->getIdentifiedby());
        }

        return mb_strtolower($this->getIdentifiedby());
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
     * @param Specimen|null $occurrenceid
     *
     * @return Determination
     */
    public function setOccurrenceid(Specimen $occurrenceid = null)
    {
        $this->occurrenceid = $occurrenceid;

        return $this;
    }

    /**
     * Get occurrenceid
     *
     * @return \AppBundle\Entity\Specimen|null
     */
    public function getOccurrenceid()
    {
        return $this->occurrenceid;
    }

    /**
     * Set taxonid
     *
     * @param Taxon|null $taxon
     *
     * @return Determination
     */
    public function setTaxon(Taxon $taxon = null)
    {
        $this->taxon = $taxon;

        return $this;
    }

    /**
     * Get taxonid
     *
     * @return \AppBundle\Entity\Taxon|null
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

    public function __toString()
    {
        if (!is_null($this->getDateidentified())) {
            return sprintf('%s %s %s',
                $this->getIdentifiedby(),
                $this->getDateidentified()->format('d/m/Y'),
                $this->getIdentificationverifstatus());
        } else {
            return sprintf('%s %s',
                $this->getIdentifiedby(),
                $this->getIdentificationverifstatus());
        }
    }

    public function toArray()
    {
        return [
            'occurrenceid' => $this->getOccurrenceid(),
            'identificationid' => $this->getIdentificationid(),
            'created' => $this->getCreated(),
            'dateidentified' => $this->getDateidentified(),
            'identificationqualifier' => $this->getIdentificationqualifier(),
            'identificationreferences' => $this->getIdentificationreferences(),
            'identificationremarks' => $this->getIdentificationremarks(),
            'identificationverifstatus' => $this->getIdentificationverifstatus(),
            'identifiedby' => $this->getIdentifiedby(),
            'modified' => $this->getModified(),
            'typestatus' => $this->getTypestatus(),
            'taxonid' => !is_null($this->getTaxon()) ? $this->getTaxon()->getTaxonid() : null,
        ];
    }
}
