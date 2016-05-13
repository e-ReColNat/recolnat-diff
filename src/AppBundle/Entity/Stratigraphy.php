<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassStratigraphy;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\StratigraphyRepository")
 * @ORM\Table(name="Stratigraphies")
 */
class Stratigraphy extends MappedSuperClassStratigraphy
{
    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Specimen", mappedBy="stratigraphy", fetch="EXTRA_LAZY")
     **/
    private $specimen;

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
     * @return mixed
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }
}
