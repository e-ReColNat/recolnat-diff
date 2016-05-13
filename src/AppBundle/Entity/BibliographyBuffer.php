<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassBibliography;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\BibliographyBufferRepository")
 * @ORM\Table(name="Bibliographies")
 */
class BibliographyBuffer extends MappedSuperClassBibliography
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SpecimenBuffer", inversedBy="bibliographies", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")
     */
    protected $specimen;

    /**
     * Get specimen
     *
     * @return \AppBundle\Entity\SpecimenBuffer
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }
}
