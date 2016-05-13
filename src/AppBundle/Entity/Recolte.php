<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassRecolte;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\RecolteRepository")
 * @ORM\Table(name="Recoltes")
 */
class Recolte extends MappedSuperClassRecolte
{

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Localisation", inversedBy="recoltes", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="locationid", referencedColumnName="locationid")
     */
    protected $localisation;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Specimen", mappedBy="recolte", fetch="EXTRA_LAZY")
     **/
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
     * @return Specimen
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    /**
     * @return Localisation
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }
}
