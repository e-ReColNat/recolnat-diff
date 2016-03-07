<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\CollectionRepository")
 * @ORM\Table(name="Collections")
 */
class Collection
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $collectionid;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $collectioncode;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $collectionname;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Institution", inversedBy="collections", fetch="LAZY")
     * @ORM\JoinColumn(name="institutionid", referencedColumnName="institutionid")
     */
    private $institution;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Specimen", mappedBy="collection")
     **/
    private $specimens;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->specimens = new ArrayCollection();
    }

    /**
     * Get collectionid
     *
     * @return integer
     */
    public function getCollectionid()
    {
        return $this->collectionid;
    }

    /**
     * Set collectioncode
     *
     * @param string $collectioncode
     *
     * @return Collection
     */
    public function setCollectioncode($collectioncode)
    {
        $this->collectioncode = $collectioncode;

        return $this;
    }

    /**
     * Get collectioncode
     *
     * @return string
     */
    public function getCollectioncode()
    {
        return $this->collectioncode;
    }

    /**
     * Set collectionname
     *
     * @param string $collectionname
     *
     * @return Collection
     */
    public function setCollectionname($collectionname)
    {
        $this->collectionname = $collectionname;

        return $this;
    }

    /**
     * Get collectionname
     *
     * @return string
     */
    public function getCollectionname()
    {
        return $this->collectionname;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Collection
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set institution
     *
     * @param Institution $institution
     *
     * @return Collection
     */
    public function setInstitution(Institution $institution = null)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institutionid
     *
     * @return Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Get specimens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpecimens()
    {
        return $this->specimens;
    }
}
