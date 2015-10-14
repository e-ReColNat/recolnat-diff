<?php

namespace AppBundle\Entity;

/**
 * ResourceManagement
 */
class ResourceManagement
{
    /**
     * @var integer
     */
    private $resourceId;

    /**
     * @var string
     */
    private $archiveUrl;

    /**
     * @var string
     */
    private $coltype;

    /**
     * @var boolean
     */
    private $isdone = '0';

    /**
     * @var string
     */
    private $key;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $sourceFileId;


    /**
     * Get resourceId
     *
     * @return integer
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Set archiveUrl
     *
     * @param string $archiveUrl
     *
     * @return ResourceManagement
     */
    public function setArchiveUrl($archiveUrl)
    {
        $this->archiveUrl = $archiveUrl;

        return $this;
    }

    /**
     * Get archiveUrl
     *
     * @return string
     */
    public function getArchiveUrl()
    {
        return $this->archiveUrl;
    }

    /**
     * Set coltype
     *
     * @param string $coltype
     *
     * @return ResourceManagement
     */
    public function setColtype($coltype)
    {
        $this->coltype = $coltype;

        return $this;
    }

    /**
     * Get coltype
     *
     * @return string
     */
    public function getColtype()
    {
        return $this->coltype;
    }

    /**
     * Set isdone
     *
     * @param boolean $isdone
     *
     * @return ResourceManagement
     */
    public function setIsdone($isdone)
    {
        $this->isdone = $isdone;

        return $this;
    }

    /**
     * Get isdone
     *
     * @return boolean
     */
    public function getIsdone()
    {
        return $this->isdone;
    }

    /**
     * Set key
     *
     * @param string $key
     *
     * @return ResourceManagement
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     *
     * @return ResourceManagement
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ResourceManagement
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set sourceFileId
     *
     * @param string $sourceFileId
     *
     * @return ResourceManagement
     */
    public function setSourceFileId($sourceFileId)
    {
        $this->sourceFileId = $sourceFileId;

        return $this;
    }

    /**
     * Get sourceFileId
     *
     * @return string
     */
    public function getSourceFileId()
    {
        return $this->sourceFileId;
    }
}
