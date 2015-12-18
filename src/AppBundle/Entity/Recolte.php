<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\RecolteRepository")
* @ORM\Table(name="Recoltes")
*/
class Recolte
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="rawid") 
     */
    private $eventid;

    /** 
     * @ORM\Column(type="integer", nullable=true, length=4)
     */
    private $decade;

    /** 
     * @ORM\Column(type="integer", nullable=true, length=2)
     */
    private $eday;

    /** 
     * @ORM\Column(type="integer", nullable=true, length=2)
     */
    private $emonth;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $eventdate;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $eventremarks;

    /** 
     * @ORM\Column(type="integer", nullable=true, length=4)
     */
    private $eyear;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $fieldnotes;

    /** 
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $fieldnumber;

    /** 
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $habitat;

    /** 
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    private $recordedby;

    /** 
     * @ORM\Column(type="integer", nullable=true, length=2)
     */
    private $sday;

    /** 
     * @ORM\Column(type="integer", nullable=true, length=2)
     */
    private $smonth;

    /** 
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $sourcefileid;

    /** 
     * @ORM\Column(type="integer", nullable=true, length=4)
     */
    private $syear;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $verbatimeventdate;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Localisation", inversedBy="recoltes", fetch="EAGER")
    * @ORM\JoinColumn(name="locationid", referencedColumnName="locationid")
    */
    private $localisation;


    /**
     * Get eventid
     *
     * @return rawid
     */
    public function getEventid()
    {
        return $this->eventid;
    }

    /**
     * Set decade
     *
     * @param integer $decade
     *
     * @return Recolte
     */
    public function setDecade($decade)
    {
        $this->decade = $decade;

        return $this;
    }

    /**
     * Get decade
     *
     * @return integer
     */
    public function getDecade()
    {
        return $this->decade;
    }

    /**
     * Set eday
     *
     * @param integer $eday
     *
     * @return Recolte
     */
    public function setEday($eday)
    {
        $this->eday = $eday;

        return $this;
    }

    /**
     * Get eday
     *
     * @return integer
     */
    public function getEday()
    {
        return $this->eday;
    }

    /**
     * Set emonth
     *
     * @param integer $emonth
     *
     * @return Recolte
     */
    public function setEmonth($emonth)
    {
        $this->emonth = $emonth;

        return $this;
    }

    /**
     * Get emonth
     *
     * @return integer
     */
    public function getEmonth()
    {
        return $this->emonth;
    }

    /**
     * Set eventdate
     *
     * @param \DateTime $eventdate
     *
     * @return Recolte
     */
    public function setEventdate($eventdate)
    {
        $this->eventdate = $eventdate;

        return $this;
    }

    /**
     * Get eventdate
     *
     * @return \DateTime
     */
    public function getEventdate()
    {
        return $this->eventdate;
    }

    /**
     * Set eventremarks
     *
     * @param string $eventremarks
     *
     * @return Recolte
     */
    public function setEventremarks($eventremarks)
    {
        $this->eventremarks = $eventremarks;

        return $this;
    }

    /**
     * Get eventremarks
     *
     * @return string
     */
    public function getEventremarks()
    {
        return $this->eventremarks;
    }

    /**
     * Set eyear
     *
     * @param integer $eyear
     *
     * @return Recolte
     */
    public function setEyear($eyear)
    {
        $this->eyear = $eyear;

        return $this;
    }

    /**
     * Get eyear
     *
     * @return integer
     */
    public function getEyear()
    {
        return $this->eyear;
    }

    /**
     * Set fieldnotes
     *
     * @param string $fieldnotes
     *
     * @return Recolte
     */
    public function setFieldnotes($fieldnotes)
    {
        $this->fieldnotes = $fieldnotes;

        return $this;
    }

    /**
     * Get fieldnotes
     *
     * @return string
     */
    public function getFieldnotes()
    {
        return $this->fieldnotes;
    }

    /**
     * Set fieldnumber
     *
     * @param string $fieldnumber
     *
     * @return Recolte
     */
    public function setFieldnumber($fieldnumber)
    {
        $this->fieldnumber = $fieldnumber;

        return $this;
    }

    /**
     * Get fieldnumber
     *
     * @return string
     */
    public function getFieldnumber()
    {
        return $this->fieldnumber;
    }

    /**
     * Set habitat
     *
     * @param string $habitat
     *
     * @return Recolte
     */
    public function setHabitat($habitat)
    {
        $this->habitat = $habitat;

        return $this;
    }

    /**
     * Get habitat
     *
     * @return string
     */
    public function getHabitat()
    {
        return $this->habitat;
    }

    /**
     * Set recordedby
     *
     * @param string $recordedby
     *
     * @return Recolte
     */
    public function setRecordedby($recordedby)
    {
        $this->recordedby = $recordedby;

        return $this;
    }

    /**
     * Get recordedby
     *
     * @return string
     */
    public function getRecordedby()
    {
        return $this->recordedby;
    }

    /**
     * Set sday
     *
     * @param integer $sday
     *
     * @return Recolte
     */
    public function setSday($sday)
    {
        $this->sday = $sday;

        return $this;
    }

    /**
     * Get sday
     *
     * @return integer
     */
    public function getSday()
    {
        return $this->sday;
    }

    /**
     * Set smonth
     *
     * @param integer $smonth
     *
     * @return Recolte
     */
    public function setSmonth($smonth)
    {
        $this->smonth = $smonth;

        return $this;
    }

    /**
     * Get smonth
     *
     * @return integer
     */
    public function getSmonth()
    {
        return $this->smonth;
    }

    /**
     * Set sourcefileid
     *
     * @param string $sourcefileid
     *
     * @return Recolte
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
     * Set syear
     *
     * @param integer $syear
     *
     * @return Recolte
     */
    public function setSyear($syear)
    {
        $this->syear = $syear;

        return $this;
    }

    /**
     * Get syear
     *
     * @return integer
     */
    public function getSyear()
    {
        return $this->syear;
    }

    /**
     * Set verbatimeventdate
     *
     * @param \DateTime $verbatimeventdate
     *
     * @return Recolte
     */
    public function setVerbatimeventdate($verbatimeventdate)
    {
        $this->verbatimeventdate = $verbatimeventdate;

        return $this;
    }

    /**
     * Get verbatimeventdate
     *
     * @return \DateTime
     */
    public function getVerbatimeventdate()
    {
        return $this->verbatimeventdate;
    }

    /**
     * Set locationid
     *
     * @param \AppBundle\Entity\Localisation $locationid
     *
     * @return Recolte
     */
    public function setLocation(\AppBundle\Entity\Localisation $locationid = null)
    {
        $this->locationid = $locationid;

        return $this;
    }

    /**
     * Get locationid
     *
     * @return \AppBundle\Entity\Localisation
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }

    public function __toString() {
        if (!is_null($this->getEventdate())) {
            return sprintf('%s %s', $this->getEventdate()->format('d/m/Y'), $this->getRecordedby());
        }
        else {
            return sprintf('%s', $this->getRecordedby());
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
                            <dir.advancedsearchlink domaine="{{result.specimendomaine}}" f1name="periode" f1value="{{result.earliestepochorlowestseries}}"></dir.advancedsearchlink> <br>
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