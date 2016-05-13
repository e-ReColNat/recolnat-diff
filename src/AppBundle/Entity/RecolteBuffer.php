<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassRecolte;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\RecolteRepository")
 * @ORM\Table(name="Recoltes")
 */
class RecolteBuffer extends MappedSuperClassRecolte
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\LocalisationBuffer", inversedBy="recoltes", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="locationid", referencedColumnName="locationid")
     */
    protected $localisation;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\SpecimenBuffer", mappedBy="recolte", fetch="EXTRA_LAZY")
     **/
    protected $specimen;

    /**
     * Get specimen
     *
     * @return SpecimenBuffer
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    /**
     * @return LocalisationBuffer
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }
}
