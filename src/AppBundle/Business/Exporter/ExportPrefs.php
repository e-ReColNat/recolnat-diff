<?php

namespace AppBundle\Business\Exporter;


class ExportPrefs
{

    const OPTIONS_SIDE_NOT_SET = [
        'sidenotset.recolnat' => 'recolnat',
        'sidenotset.institution' => 'institution'
    ];

    const OPTIONS_NEW_RECORDS = [
        'newrecords.recolnat' => 'recolnat',
        'newrecords.institution' => 'institution',
        'newrecords.both' => 'both'
    ];
    /**
     * Choix pour les enregistrements
     * @var string
     */
    protected $sideForChoicesNotSet;

    /**
     * @var string
     */
    protected $sideForNewRecords;

    /**
     * @var string
     */
    protected $cookieTGC;

    /**
     * @return string
     */
    public function getSideForChoicesNotSet()
    {
        return $this->sideForChoicesNotSet;
    }

    /**
     * @param string $sideForChoicesNotSet
     */
    public function setSideForChoicesNotSet($sideForChoicesNotSet)
    {
        $this->sideForChoicesNotSet = $sideForChoicesNotSet;
    }

    /**
     * @return string
     */
    public function getSideForNewRecords()
    {
        return $this->sideForNewRecords;
    }

    /**
     * @param string $sideForNewRecords
     */
    public function setSideForNewRecords($sideForNewRecords)
    {
        $this->sideForNewRecords = $sideForNewRecords;
    }

    /**
     * @return string
     */
    public function getCookieTGC()
    {
        return $this->cookieTGC;
    }

    /**
     * @param string $cookieTGC
     */
    public function setCookieTGC($cookieTGC)
    {
        $this->cookieTGC = $cookieTGC;
    }


}
