<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 11/02/16
 * Time: 16:56
 */

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
        'newrecords.priority.recolnat' => 'bothRecolnat',
        'newrecords.priority.institution' => 'bothInstitution',
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
}