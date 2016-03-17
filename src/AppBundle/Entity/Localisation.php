<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LocalisationRepository")
 * @ORM\Table(name="Localisations")
 */
class Localisation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     */
    private $locationid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $averagealtituderounded;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $continent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $countrycode;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $county;

    /**
     * @ORM\Column(type="float", length=24, nullable=true, options={"precision"=24, "scale"=0})
     */
    private $decimallatitude;

    /**
     * @ORM\Column(type="float", length=24, nullable=true, options={"precision"=24, "scale"=0})
     */
    private $decimallongitude;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $geodeticdatum;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $georeferencesources;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hascoordinates;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $locality;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $locationremarks;

    /**
     * @ORM\Column(type="float", length=24, nullable=true, options={"precision"=24, "scale"=0})
     */
    private $maximumdepthinmeters;

    /**
     * @ORM\Column(type="float", length=24, nullable=true, options={"precision"=24, "scale"=0})
     */
    private $maximumelevationinmeters;

    /**
     * @ORM\Column(type="float", length=24, nullable=true, options={"precision"=24, "scale"=0})
     */
    private $minimumdepthinmeters;

    /**
     * @ORM\Column(type="float", length=24, nullable=true, options={"precision"=24, "scale"=0})
     */
    private $minimumelevationinmeters;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $municipality;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stateprovince;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $verbatimcountry;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $verbatimelevation;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $verbatimlocality;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Recolte", mappedBy="localisation", fetch="EXTRA_LAZY")
     */
    private $recoltes;

    public function __construct()
    {
        $this->recoltes = new ArrayCollection();
    }

    /**
     * Get locationid
     *
     * @return integer
     */
    public function getLocationid()
    {
        return $this->locationid;
    }

    /**
     * Set averagealtituderounded
     *
     * @param integer $averagealtituderounded
     *
     * @return Localisation
     */
    public function setAveragealtituderounded($averagealtituderounded)
    {
        $this->averagealtituderounded = $averagealtituderounded;

        return $this;
    }

    /**
     * Get averagealtituderounded
     *
     * @return integer
     */
    public function getAveragealtituderounded()
    {
        return $this->averagealtituderounded;
    }

    /**
     * Set continent
     *
     * @param string $continent
     *
     * @return Localisation
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * Get continent
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Localisation
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set countrycode
     *
     * @param string $countrycode
     *
     * @return Localisation
     */
    public function setCountrycode($countrycode)
    {
        $this->countrycode = $countrycode;

        return $this;
    }

    /**
     * Get countrycode
     *
     * @return string
     */
    public function getCountrycode()
    {
        return $this->countrycode;
    }

    /**
     * Set county
     *
     * @param string $county
     *
     * @return Localisation
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set decimallatitude
     *
     * @param float $decimallatitude
     *
     * @return Localisation
     */
    public function setDecimallatitude($decimallatitude)
    {
        $this->decimallatitude = $decimallatitude;

        return $this;
    }

    /**
     * Get decimallatitude
     *
     * @return float
     */
    public function getDecimallatitude()
    {
        return $this->decimallatitude;
    }

    /**
     * Set decimallongitude
     *
     * @param float $decimallongitude
     *
     * @return Localisation
     */
    public function setDecimallongitude($decimallongitude)
    {
        $this->decimallongitude = $decimallongitude;

        return $this;
    }

    /**
     * Get decimallongitude
     *
     * @return float
     */
    public function getDecimallongitude()
    {
        return $this->decimallongitude;
    }

    /**
     * Set geodeticdatum
     *
     * @param string $geodeticdatum
     *
     * @return Localisation
     */
    public function setGeodeticdatum($geodeticdatum)
    {
        $this->geodeticdatum = $geodeticdatum;

        return $this;
    }

    /**
     * Get geodeticdatum
     *
     * @return string
     */
    public function getGeodeticdatum()
    {
        return $this->geodeticdatum;
    }

    /**
     * Set georeferencesources
     *
     * @param string $georeferencesources
     *
     * @return Localisation
     */
    public function setGeoreferencesources($georeferencesources)
    {
        $this->georeferencesources = $georeferencesources;

        return $this;
    }

    /**
     * Get georeferencesources
     *
     * @return string
     */
    public function getGeoreferencesources()
    {
        return $this->georeferencesources;
    }

    /**
     * Set hascoordinates
     *
     * @param boolean $hascoordinates
     *
     * @return Localisation
     */
    public function setHascoordinates($hascoordinates)
    {
        $this->hascoordinates = $hascoordinates;

        return $this;
    }

    /**
     * Get hascoordinates
     *
     * @return boolean
     */
    public function getHascoordinates()
    {
        return $this->hascoordinates;
    }

    /**
     * Set locality
     *
     * @param string $locality
     *
     * @return Localisation
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * Get locality
     *
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * Set locationremarks
     *
     * @param string $locationremarks
     *
     * @return Localisation
     */
    public function setLocationremarks($locationremarks)
    {
        $this->locationremarks = $locationremarks;

        return $this;
    }

    /**
     * Get locationremarks
     *
     * @return string
     */
    public function getLocationremarks()
    {
        return $this->locationremarks;
    }

    /**
     * Set maximumdepthinmeters
     *
     * @param float $maximumdepthinmeters
     *
     * @return Localisation
     */
    public function setMaximumdepthinmeters($maximumdepthinmeters)
    {
        $this->maximumdepthinmeters = $maximumdepthinmeters;

        return $this;
    }

    /**
     * Get maximumdepthinmeters
     *
     * @return float
     */
    public function getMaximumdepthinmeters()
    {
        return $this->maximumdepthinmeters;
    }

    /**
     * Set maximumelevationinmeters
     *
     * @param float $maximumelevationinmeters
     *
     * @return Localisation
     */
    public function setMaximumelevationinmeters($maximumelevationinmeters)
    {
        $this->maximumelevationinmeters = $maximumelevationinmeters;

        return $this;
    }

    /**
     * Get maximumelevationinmeters
     *
     * @return float
     */
    public function getMaximumelevationinmeters()
    {
        return $this->maximumelevationinmeters;
    }

    /**
     * Set minimumdepthinmeters
     *
     * @param float $minimumdepthinmeters
     *
     * @return Localisation
     */
    public function setMinimumdepthinmeters($minimumdepthinmeters)
    {
        $this->minimumdepthinmeters = $minimumdepthinmeters;

        return $this;
    }

    /**
     * Get minimumdepthinmeters
     *
     * @return float
     */
    public function getMinimumdepthinmeters()
    {
        return $this->minimumdepthinmeters;
    }

    /**
     * Set minimumelevationinmeters
     *
     * @param float $minimumelevationinmeters
     *
     * @return Localisation
     */
    public function setMinimumelevationinmeters($minimumelevationinmeters)
    {
        $this->minimumelevationinmeters = $minimumelevationinmeters;

        return $this;
    }

    /**
     * Get minimumelevationinmeters
     *
     * @return float
     */
    public function getMinimumelevationinmeters()
    {
        return $this->minimumelevationinmeters;
    }

    /**
     * Set municipality
     *
     * @param string $municipality
     *
     * @return Localisation
     */
    public function setMunicipality($municipality)
    {
        $this->municipality = $municipality;

        return $this;
    }

    /**
     * Get municipality
     *
     * @return string
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * Set stateprovince
     *
     * @param string $stateprovince
     *
     * @return Localisation
     */
    public function setStateprovince($stateprovince)
    {
        $this->stateprovince = $stateprovince;

        return $this;
    }

    /**
     * Get stateprovince
     *
     * @return string
     */
    public function getStateprovince()
    {
        return $this->stateprovince;
    }

    /**
     * Set verbatimcountry
     *
     * @param string $verbatimcountry
     *
     * @return Localisation
     */
    public function setVerbatimcountry($verbatimcountry)
    {
        $this->verbatimcountry = $verbatimcountry;

        return $this;
    }

    /**
     * Get verbatimcountry
     *
     * @return string
     */
    public function getVerbatimcountry()
    {
        return $this->verbatimcountry;
    }

    /**
     * Set verbatimelevation
     *
     * @param string $verbatimelevation
     *
     * @return Localisation
     */
    public function setVerbatimelevation($verbatimelevation)
    {
        $this->verbatimelevation = $verbatimelevation;

        return $this;
    }

    /**
     * Get verbatimelevation
     *
     * @return string
     */
    public function getVerbatimelevation()
    {
        return $this->verbatimelevation;
    }

    /**
     * Set verbatimlocality
     *
     * @param string $verbatimlocality
     *
     * @return Localisation
     */
    public function setVerbatimlocality($verbatimlocality)
    {
        $this->verbatimlocality = $verbatimlocality;

        return $this;
    }

    /**
     * Get verbatimlocality
     *
     * @return string
     */
    public function getVerbatimlocality()
    {
        return $this->verbatimlocality;
    }

    /**
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRecoltes()
    {
        return $this->recoltes;
    }

    public function __toString()
    {
        return sprintf('%s (%s) %s %s %s', $this->verbatimcountry, $this->country, $this->county, $this->locality,
            $this->verbatimlocality);
    }

    public function toArray()
    {
        return [
            'locationid' => $this->getLocationid(),
            'averagealtituderounded' => $this->getAveragealtituderounded(),
            'continent' => $this->getContinent(),
            'country' => $this->getCountry(),
            'countrycode' => $this->getCountrycode(),
            'county' => $this->getCounty(),
            'decimallatitude' => $this->getDecimallatitude(),
            'decimallongitude' => $this->getDecimallongitude(),
            'geodeticdatum' => $this->getGeodeticdatum(),
            'georeferencesources' => $this->getGeoreferencesources(),
            'hascoordinates' => $this->getHascoordinates(),
            'locality' => $this->getLocality(),
            'locationremarks' => $this->getLocationremarks(),
            'maximumdepthinmeters' => $this->getMaximumdepthinmeters(),
            'maximumelevationinmeters' => $this->getMaximumelevationinmeters(),
            'minimumdepthinmeters' => $this->getMinimumdepthinmeters(),
            'minimumelevationinmeters' => $this->getMinimumelevationinmeters(),
            'municipality' => $this->getMunicipality(),
            'stateprovince' => $this->getStateprovince(),
            'verbatimcountry' => $this->getVerbatimcountry(),
            'verbatimelevation' => $this->getVerbatimelevation(),
            'verbatimlocality' => $this->getVerbatimlocality(),
        ];
    }
}