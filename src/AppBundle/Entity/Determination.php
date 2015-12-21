<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\DeterminationRepository")
* @ORM\Table(name="Determinations")
*/
class Determination
{
    /**
    * @ORM\Id
    * @ORM\Column(type="rawid", length=16, name="identificationid", nullable=false)
    */
    private $identificationid;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateidentified;

    /** 
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $dwcaidentificationid;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $identificationqualifier;

    /** 
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $identificationreferences;

    /** 
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $identificationremarks;

    /** 
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $identificationverifstatus;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    private $identifiedby;

     /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified;

    /** 
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $sourcefileid;

    /** 
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $typestatus;

    /**
    * @var \AppBundle\Entity\Specimen
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Specimen", inversedBy="determinations", fetch="EXTRA_LAZY")
    * @ORM\JoinColumn(name="occurrenceid", referencedColumnName="occurrenceid")
    */
    protected $specimen;

    /**
     * @ORM\OneToOne(targetEntity="Taxon", inversedBy="determination", fetch="EAGER")
     * @ORM\JoinColumn(name="taxonid", referencedColumnName="taxonid")
     **/
    private $taxon;


    /**
     * Get identificationid
     *
     * @return guid
     */
    public function getIdentificationid()
    {
        return $this->identificationid;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Determination
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set dateidentified
     *
     * @param \DateTime $dateidentified
     *
     * @return Determination
     */
    public function setDateidentified($dateidentified)
    {
        $this->dateidentified = $dateidentified;

        return $this;
    }

    /**
     * Get dateidentified
     *
     * @return \DateTime
     */
    public function getDateidentified()
    {
        return $this->dateidentified;
    }

    /**
     * Set dwcaidentificationid
     *
     * @param string $dwcaidentificationid
     *
     * @return Determination
     */
    public function setDwcaidentificationid($dwcaidentificationid)
    {
        $this->dwcaidentificationid = $dwcaidentificationid;

        return $this;
    }

    /**
     * Get dwcaidentificationid
     *
     * @return string
     */
    public function getDwcaidentificationid()
    {
        return $this->dwcaidentificationid;
    }

    /**
     * Set identificationqualifier
     *
     * @param string $identificationqualifier
     *
     * @return Determination
     */
    public function setIdentificationqualifier($identificationqualifier)
    {
        $this->identificationqualifier = $identificationqualifier;

        return $this;
    }

    /**
     * Get identificationqualifier
     *
     * @return string
     */
    public function getIdentificationqualifier()
    {
        return $this->identificationqualifier;
    }

    /**
     * Set identificationreferences
     *
     * @param string $identificationreferences
     *
     * @return Determination
     */
    public function setIdentificationreferences($identificationreferences)
    {
        $this->identificationreferences = $identificationreferences;

        return $this;
    }

    /**
     * Get identificationreferences
     *
     * @return string
     */
    public function getIdentificationreferences()
    {
        return $this->identificationreferences;
    }

    /**
     * Set identificationremarks
     *
     * @param string $identificationremarks
     *
     * @return Determination
     */
    public function setIdentificationremarks($identificationremarks)
    {
        $this->identificationremarks = $identificationremarks;

        return $this;
    }

    /**
     * Get identificationremarks
     *
     * @return string
     */
    public function getIdentificationremarks()
    {
        return $this->identificationremarks;
    }

    /**
     * Set identificationverifstatus
     *
     * @param integer $identificationverifstatus
     *
     * @return Determination
     */
    public function setIdentificationverifstatus($identificationverifstatus)
    {
        $this->identificationverifstatus = $identificationverifstatus;

        return $this;
    }

    /**
     * Get identificationverifstatus
     *
     * @return integer
     */
    public function getIdentificationverifstatus()
    {
        return $this->identificationverifstatus;
    }

    /**
     * Set identifiedby
     *
     * @param string $identifiedby
     *
     * @return Determination
     */
    public function setIdentifiedby($identifiedby)
    {
        $this->identifiedby = $identifiedby;

        return $this;
    }

    /**
     * Get identifiedby
     *
     * @return string
     */
    public function getIdentifiedby()
    {
        return $this->identifiedby;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Determination
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Determination
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

    /**
     * Set typestatus
     *
     * @param string $typestatus
     *
     * @return Determination
     */
    public function setTypestatus($typestatus)
    {
        $this->typestatus = $typestatus;

        return $this;
    }

    /**
     * Get typestatus
     *
     * @return string
     */
    public function getTypestatus()
    {
        return $this->typestatus;
    }

    /**
     * Set occurrenceid
     *
     * @param \AppBundle\Entity\Specimen $occurrenceid
     *
     * @return Determination
     */
    public function setOccurrenceid(\AppBundle\Entity\Specimen $occurrenceid = null)
    {
        $this->occurrenceid = $occurrenceid;

        return $this;
    }

    /**
     * Get occurrenceid
     *
     * @return \AppBundle\Entity\Specimen
     */
    public function getOccurrenceid()
    {
        return $this->occurrenceid;
    }

    /**
     * Set taxonid
     *
     * @param \AppBundle\Entity\Taxon $taxon
     *
     * @return Determination
     */
    public function setTaxon(\AppBundle\Entity\Taxon $taxon = null)
    {
        $this->taxon = $taxon;

        return $this;
    }

    /**
     * Get taxonid
     *
     * @return \AppBundle\Entity\Taxon
     */
    public function getTaxon()
    {
        return $this->taxon;
    }
    
    /**
     * Get specimen
     *
     * @return \AppBundle\Entity\Specimen
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    public function __toString()
    {
        if (!is_null($this->getDateidentified())) {
            return sprintf('%s %s %s', 
                    $this->getIdentifiedby(), 
                    $this->getDateidentified()->format('d/m/Y'), 
                    $this->getIdentificationverifstatus());
        }
        else {
            return sprintf('%s %s', 
                    $this->getIdentifiedby(), 
                    $this->getIdentificationverifstatus());
        }
    }
}
/*
 
<div>
    <div ng-repeat="result in results | filter:query  | orderBy:orderProp | orderBy:'occurrenceid' track by result['_id']" ng-class-odd="'odd'">

        <div class="row no-margin"> <br/>
            <div class="col-sm-1 col-sm-offset-0" >
                <div class="btn-toolbar" role="toolbar" style="padding-left: 80%;">
                    <input type="checkbox" ng-checked="checkboxSelectAll.all||result.checked" ng-model="result.checked" ng-click="specimenCheck(result._id,result.checked)">
                </div>
            </div>
            <div class="col-sm-9 col-sm-offset-0">
                <a href="#/specimen/{{result.specimendomaine}}/{{ result._id }}">
                    <span ng-if="result.family!=null"><span ng-bind-html="result.family | unsafe"> </span> / </span>
                    <span ng-if="result.genus!=null"><i><span ng-bind-html="result.genus | unsafe"></span></i> / </span>
                    <span ng-if="result.scientificname!=null"><i><span ng-bind-html="result.scientificname | unsafe"></span></i> / </span>
                    <span ng-if="result.scientificnameauthorship!=null"><span ng-bind-html="result.scientificnameauthorship | unsafe"> </span> / </span>
                    <span ng-if="result.catalognumber!=null" ng-bind-html="result.catalognumber | unsafe"></span>
                </a>
                <span class="row no-margin"><br />
                    <div class="col-sm-6 col-sm-offset-0">
                        {{"Institution" | translate }}&nbsp; <span ng-bind-html="result.institutioncode | unsafe"></span><br>
                        {{"Collection" | translate }}&nbsp;<dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="collectioncode"
                                                                                    f1value="{{result.collectioncode}}"></dir.advancedsearchlink>                <br>
                        {{'Famille' | translate }}&nbsp;<dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="famille" f1value="{{result.family}}"></dir.advancedsearchlink>                <br>
                        {{'Genre' | translate }}&nbsp; <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="genre" f1value="{{result.genus}}"></dir.advancedsearchlink>       <br>
                        {{'Binom' | translate }}&nbsp; <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="nomscientifique" f1value="{{result.scientificname}}"></dir.advancedsearchlink>        <br>
                    </div>
                    <div class="col-sm-6">
                        <span ng-if="result.country != null">
                            {{'Pays Etiquette' | translate }}
                            <dir.advancedsearchlink domaine="{{result.specimendomaine}}"
                                                    f1name="localisationvalue" f1value="{{result.country}}"
                                                    f2name="localisationtype"  f2value="pays"
                                                    foverridedisplay="{{result.verbatimcountry}}"
                                    ></dir.advancedsearchlink>
                            <span ng-if="result.countrycode!=null">({{result.countrycode}})</span><br />
                        </span>
                        <span ng-if="result.county != null">
                            {{"Département"|translate}} {{result.county}}
                        </span>
                        <span ng-if="result.locality != null">
                             {{"Localité" | translate }}&nbsp; <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="localisationvalue" f1value="{{result.locality}}" f2name="localisationtype" f2value="localite"></dir.advancedsearchlink>  <br>
                        </span>
                        <span ng-if="result.verbatimlocality != null">
                             {{"Localité originale" | translate }}&nbsp; <span ng-bind-html="result.verbatimlocality | unsafe"></span><br>
                        </span>
                        <span ng-if="result.recordedby != null">
                            {{'Nom du collecteur' | translate }}&nbsp;
                            <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="recolteur" f1value="{{result.recordedby}}"></dir.advancedsearchlink> <br>
                        </span>
                        <span ng-if="result.fieldnumber != null">
                            {{'Numéro de récolte' | translate }}&nbsp;
                            <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="coderecolte" f1value="{{result.fieldnumber}}" f2name="recolteur" f2value="{{result.recordedby}}"></dir.advancedsearchlink> <br>
                        </span>
                        <span ng-if="result.ladaterecolte != ''">
                            {{"Date de récolte" | translate }}&nbsp {{result.ladaterecolte}}
                        </span>
                        <div ng-if="domaine == paleontologie">
                            <span ng-if="result.earliestperiodorlowestsystem != null">
                            {{'Système' | translate }}&nbsp;
                            <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="periode" f1value="{{result.earliestperiodorlowestsystem}}"></dir.advancedsearchlink> <br>
                            </span>
                            <span ng-if="result.earliestepochorlowestseries != null">
                            {{'Epoque' | translate }}&nbsp;
                            <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="periode" f1value="{{result.earliestepochorlowestseries earliestperiodorlowestsystem earliestageorloweststage}}"></dir.advancedsearchlink> <br>
                            </span>
                            <span ng-if="result.earliestageorloweststage != null">
                            {{'Etage' | translate }}&nbsp;
                            <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="periode" f1value="{{result.earliestageorloweststage}}"></dir.advancedsearchlink> <br>
                            </span>
                        </div><br />

                    </div>
                </span>
                <span class="row no-margin">
                    <span class="col-sm-6 col-xs-offset-6">
                        <button type="button" class="btn btn-default btn-xs" ng-click="isHideSSOptions=true;" ng-hide="isHideSSOptions">
                            <span class="glyphicon glyphicon-chevron-down"></span>
                        </button>
                        <button type="button" class="btn btn-default btn-xs" ng-click="isHideSSOptions=false;" ng-hide="!isHideSSOptions">
                            <span class="glyphicon glyphicon-chevron-up"></span>
                        </button>
                    </span>
                </span>
            </div>
            <div class="col-sm-2 col-sm-offset-0 no-padding" style="position:relative;" ng-show="result.images.length > 0 ">
                <img class="round-corner zoomcursor" style="max-width:125px" data-spinner-on-load
                     ng-src="{{result.images[0].thumburl}}" ng-click="visionneusePopup(result)" >
                <span class="glyphicon glyphicon-copyright-mark copyright-button" style="height: 10px"
                      dir.popover popoverdata="result.images[0].copyright" popovertitle="Crédits"></span>
            </div>
        </div>

        <div class="row no-margin" ng-if="isHideSSOptions">
            <dir.similarspecimens index="{{result.specimendomaine}}" famille="{{result.family}}" genre="{{result.genus}}"></dir.similarspecimens>
        </div>
        <br />
    </div>
</div>

 */