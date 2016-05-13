<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassBibliography;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\BibliographyRepository")
 * @ORM\Table(name="Bibliographies")
 */
class Bibliography extends MappedSuperClassBibliography
{
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
     * Get specimen
     *
     * @return \AppBundle\Entity\Specimen
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }
}
