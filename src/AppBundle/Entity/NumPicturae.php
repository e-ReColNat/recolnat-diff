<?php

namespace AppBundle\Entity;

/**
 * NumPicturae
 */
class NumPicturae
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $nombreLigne;

    /**
     * @var string
     */
    private $nomDuLot;

    /**
     * @var \DateTime
     */
    private $traiteLe;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombreLigne
     *
     * @param integer $nombreLigne
     *
     * @return NumPicturae
     */
    public function setNombreLigne($nombreLigne)
    {
        $this->nombreLigne = $nombreLigne;

        return $this;
    }

    /**
     * Get nombreLigne
     *
     * @return integer
     */
    public function getNombreLigne()
    {
        return $this->nombreLigne;
    }

    /**
     * Set nomDuLot
     *
     * @param string $nomDuLot
     *
     * @return NumPicturae
     */
    public function setNomDuLot($nomDuLot)
    {
        $this->nomDuLot = $nomDuLot;

        return $this;
    }

    /**
     * Get nomDuLot
     *
     * @return string
     */
    public function getNomDuLot()
    {
        return $this->nomDuLot;
    }

    /**
     * Set traiteLe
     *
     * @param \DateTime $traiteLe
     *
     * @return NumPicturae
     */
    public function setTraiteLe($traiteLe)
    {
        $this->traiteLe = $traiteLe;

        return $this;
    }

    /**
     * Get traiteLe
     *
     * @return \DateTime
     */
    public function getTraiteLe()
    {
        return $this->traiteLe;
    }
}
