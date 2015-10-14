<?php

namespace AppBundle\Entity;

/**
 * MultimediaHasOccurrences
 */
class MultimediaHasOccurrences
{
    /**
     * @var guid
     */
    private $multimediaid;

    /**
     * @var guid
     */
    private $occurrenceid;


    /**
     * Set multimediaid
     *
     * @param guid $multimediaid
     *
     * @return MultimediaHasOccurrences
     */
    public function setMultimediaid($multimediaid)
    {
        $this->multimediaid = $multimediaid;

        return $this;
    }

    /**
     * Get multimediaid
     *
     * @return guid
     */
    public function getMultimediaid()
    {
        return $this->multimediaid;
    }

    /**
     * Set occurrenceid
     *
     * @param guid $occurrenceid
     *
     * @return MultimediaHasOccurrences
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
