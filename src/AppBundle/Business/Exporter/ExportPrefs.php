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

    /*
     * <p>Il y a {{ sumStats.diffs - statsChoices.sum }} choix non effectués</p>
<p>Il y a {{ sumLonesomeRecords.recolnat }} nouveaux enregistrements du côté de recolnat</p>
<p>Il y a {{ sumLonesomeRecords.institution }} nouveaux enregistrements du côté de l'institution</p>

<p>Pour les choix non effectués souhaitez :
    <label>Récupérer les enregistrements de Recolnat</label><input type="radio" name="choicesNotSet" value="recolnat">
    <label>Récupérer les enregistrements de l'institution</label><input type="radio" name="choicesNotSet"
                                                                        value="institution">
</p>

<p>
    Pour les nouveaux enregistrements souhaitez :
    <label>Récupérer les enregistrements de Recolnat</label><input type="radio" name="newRecords" value="recolnat">
    <label>Récupérer les enregistrements de l'institution</label><input type="radio" name="newRecords"
                                                                        value="institution">
    <p>Récupérer les enregistrements des deux côtés si possible</p>
    <label>Avec la priorité Recolnat en cas de conflit</label><input type="radio" name="newRecords" value="bothRecolnat">
    <label>Avec la priorité Insttitution en cas de conflit</label><input type="radio" name="newRecords" value="BothInstitution">
     */

    const OPTIONS_SIDE_NOT_SET = [
        'sidenotset.recolnat'=>'recolnat',
        'sidenotset.institution'=>'institution'
    ];

    const OPTIONS_NEW_RECORDS = [
        'newrecords.recolnat'=>'recolnat',
        'newrecords.institution'=>'institution',
        'newrecords.priority.recolnat'=>'bothRecolnat',
        'newrecords.priority.institution'=>'bothInstitution',
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