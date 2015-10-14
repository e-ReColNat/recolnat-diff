<?php

namespace AppBundle\Entity;

/**
 * UuidsForExport
 */
class UuidsForExport
{
    /**
     * @var integer
     */
    private $rnum;

    /**
     * @var guid
     */
    private $occurrenceid;


    /**
     * Get rnum
     *
     * @return integer
     */
    public function getRnum()
    {
        return $this->rnum;
    }

    /**
     * Set occurrenceid
     *
     * @param guid $occurrenceid
     *
     * @return UuidsForExport
     */
    public function setOccurrenceid($occurrenceid)
    {
        $this->occurrenceid = $occurrenceid;

        return $this;
    }

    /**
     * Get occurrenceid
     *
     * @return guid
     */
    public function getOccurrenceid()
    {
        return $this->occurrenceid;
    }
}
