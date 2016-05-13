<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassStratigraphy;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\StratigraphyBufferRepository")
 * @ORM\Table(name="Stratigraphies")
 */
class StratigraphyBuffer extends MappedSuperClassStratigraphy
{
    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\SpecimenBuffer", mappedBy="stratigraphy", fetch="EXTRA_LAZY")
     **/
    private $specimen;

    /**
     * @return mixed
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }
}
