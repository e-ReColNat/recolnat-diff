<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassLocalisation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LocalisationRepository")
 * @ORM\Table(name="Localisations")
 */
class LocalisationBuffer extends MappedSuperClassLocalisation
{
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RecolteBuffer", mappedBy="localisation", fetch="EXTRA_LAZY")
     */
    protected $recoltes;

    /**
     *
     * @return ArrayCollection
     */
    public function getRecoltes()
    {
        return $this->recoltes;
    }
}
