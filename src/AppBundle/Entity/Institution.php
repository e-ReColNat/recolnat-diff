<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\InstitutionRepository")
 * @ORM\Table(name="Institutions")
 */
class Institution
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $institutionid;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $institutioncode;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $sourcefileid;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Collection", mappedBy="institution", fetch="LAZY")
     */
    private $collections;

    /**
     * Get institutionid
     *
     * @return integer
     */
    public function getInstitutionid()
    {
        return $this->institutionid;
    }

    /**
     * Set institutioncode
     *
     * @param string $institutioncode
     *
     * @return Institution
     */
    public function setInstitutioncode($institutioncode)
    {
        $this->institutioncode = $institutioncode;

        return $this;
    }

    /**
     * Get institutioncode
     *
     * @return string
     */
    public function getInstitutioncode()
    {
        return $this->institutioncode;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Institution
     */
    public function setSourcefileid($sourcefileid)
    {
        $this->sourcefileid = $sourcefileid;

        return $this;
    }

    /**
     * Get sourcefileid
     *
     * @return string
     */
    public function getSourcefileid()
    {
        return $this->sourcefileid;
    }

    public function getCollections()
    {
        return $this->collections;
    }
}
