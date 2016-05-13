<?php

namespace AppBundle\Entity;

use AppBundle\Entity\SuperClass\MappedSuperClassSpecimen;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\SpecimenBufferRepository")
 * @ORM\Table(name="Specimens")
 */
class SpecimenBuffer extends MappedSuperClassSpecimen
{

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Collection", inversedBy="specimens", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="collectionid", referencedColumnName="collectionid")
     **/
    protected $collection;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\StratigraphyBuffer", fetch="EXTRA_LAZY", inversedBy="specimen")
     * @ORM\JoinColumn(name="geologicalcontextid", referencedColumnName="geologicalcontextid")
     **/
    protected $stratigraphy;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\RecolteBuffer", inversedBy="specimen", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="eventid", referencedColumnName="eventid")
     **/
    protected $recolte;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Multimedia", inversedBy="specimens")
     * @ORM\JoinTable(name="Multimedia_Has_Occurrences",
     *      joinColumns={@ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="multimediaid", referencedColumnName="multimediaid")}
     *      )
     **/
    protected $multimedias;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Determination", mappedBy="specimen", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"identificationverifstatus" = "DESC", "dateidentified" = "DESC"})
     */
    protected $determinations;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\BibliographyBuffer", mappedBy="specimen", fetch="EXTRA_LAZY")
     */
    protected $bibliographies;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->multimedias = new ArrayCollection();
        $this->determinations = new ArrayCollection();
        $this->bibliographies = new ArrayCollection();
    }
}
