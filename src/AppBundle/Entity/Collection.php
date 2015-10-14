<?php

namespace AppBundle\Entity;

/**
 * Collection
 */
class Collection
{
    /**
     * @var integer
     */
    private $collectionid;

    /**
     * @var string
     */
    private $collectioncode;

    /**
     * @var string
     */
    private $collectionname;

    /**
     * @var string
     */
    private $type = 'default';

    /**
     * @var \AppBundle\Entity\Institution
     */
    private $institutionid;


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
     * Set institutionid
     *
     * @param \AppBundle\Entity\Institution $institutionid
     *
     * @return Collection
     */
    public function setInstitutionid(\AppBundle\Entity\Institution $institutionid = null)
    {
        $this->institutionid = $institutionid;

        return $this;
    }

    /**
     * Get institutionid
     *
     * @return \AppBundle\Entity\Institution
     */
    public function getInstitutionid()
    {
        return $this->institutionid;
    }
}
