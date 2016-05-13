<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassLocalisation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LocalisationRepository")
 * @ORM\Table(name="Localisations")
 */
class Localisation extends MappedSuperClassLocalisation
{
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Recolte", mappedBy="localisation", fetch="EXTRA_LAZY")
     */
    protected $recoltes;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $modified;

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
     *
     * @return ArrayCollection
     */
    public function getRecoltes()
    {
        return $this->recoltes;
    }
}
