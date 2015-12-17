<?php
namespace AppBundle\Business\Exporter;
use AppBundle\Business\Exporter\AbstractExporter;
/**
 * Description of DwcExporter
 *
 * @author tpateffoz
 */
class DwcExporter extends AbstractExporter
{
    public function __construct($datas)
    {
        parent::__construct($datas);
        //return $this->generateXmlMeta();
    }
    
    public function generateXmlMeta() {
        $dwc = new \DOMDocument('1.0', 'UTF-8');
        $dwc->preserveWhiteSpace = false;
        $dwc->formatOutput = true;
        $root=$dwc->createElement('archive');
        $root->setAttribute('xmlns', 'http://rs.tdwg.org/dwc/text/') ;
        $dwc->appendChild($root) ;
        foreach ($this->datas as $className => $row) {
            $this->generateXmlHeader($dwc, $root, $className, array_keys($row[0]));
        }
        return $dwc->saveXML($root);
    }
    private function generateXmlHeader($dwc, $root, $extension, $keys) {
        switch (strtolower($extension)) {
            case 'specimen' :
                $this->getXmlSpecimen($dwc, $root, $keys) ;
                break;
            case 'bibliography' :
                return $this->getXmlBibliography($dwc, $root, $keys) ;
            case 'taxon' :
                $this->getXmlTaxon($dwc, $root, $keys) ;
                break;
            case 'determination' :
                $this->getXmlDetermination($dwc, $root, $keys) ;
                break;
            case 'localisation' :
                $this->getXmlLocalisation($dwc, $root, $keys) ;
                break;
            case 'recolte' :
                $this->getXmlRecolte($dwc, $root, $keys) ;
                break;
            case 'stratigraphy' :
                $this->getXmlStratigraphy($dwc, $root, $keys) ;
                break;
        }
    }

    private function setCsvParameterNode(\DOMElement &$node, $rowType) {
        $node->setAttribute('encoding', 'UTF-8') ;
        $node->setAttribute('fieldsTerminatedBy', '\t') ;
        $node->setAttribute('linesTerminatedBy', '\n') ;
        $node->setAttribute('fieldsEnclosedBy', '') ;
        $node->setAttribute('ignoreHeaderLines', '1') ;
        $node->setAttribute('rowType', $rowType) ;
    }
    /**
     * 
     * @param type $keys
     * @return \DOMDocument
     */
    private function getXmlSpecimen(\DOMDocument $dwc, $root, $keys) {
        $coreNode = $dwc->createElement('core') ;
        $this->setCsvParameterNode($coreNode, 'http://rs.gbif.org/terms/1.0/Occurrence');
        $root->appendChild($coreNode) ;
        $fileNode = $dwc->createElement('file', 'occurrence.csv') ;
        $coreNode->appendChild($fileNode) ;
        $compt=0;
        foreach ($keys as $key=>$value) {
            $term='';
            switch ($value) {
                case 'occurrenceid':
                    $node = $dwc->createElement('id');
                    $node->setAttribute('index', $key);
                    $coreNode->appendChild($node) ;
                    $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                    break;
                case 'accessrights':
                    $term = 'http://purl.org/dc/terms/accessRights';
                    break;
                case 'associatedmedia':
                    $term = 'http://rs.tdwg.org/dwc/terms/associatedMedia';
                    break;
                case 'associatedreferences':
                    $term = 'http://rs.tdwg.org/dwc/terms/associatedReferences';
                    break;
                case 'associatedtaxa':
                    $term = 'http://rs.tdwg.org/dwc/terms/associatedTaxa';
                    break;
                case 'basisofrecord':
                    $term = 'http://rs.tdwg.org/dwc/terms/basisOfRecord';
                    break;
                case 'bibliographiccitation':
                    $term = 'http://purl.org/dc/terms/bibliographicCitation';
                    break;
                case 'catalognumber':
                    $term = 'http://rs.tdwg.org/dwc/terms/catalogNumber';
                    break;
                case 'collectioncode':
                    $term = 'http://rs.tdwg.org/dwc/terms/collectionCode';
                    break;
                case 'created':
                    $term = 'http://purl.org/dc/terms/created';
                    break;
                case 'disposition':
                    $term = 'http://rs.tdwg.org/dwc/terms/disposition';
                    break;
                case 'dwcaid':
                    $term = '';
                    break;
                case 'hascoordinates':
                    $term = '';
                    break;
                case 'hasmedia':
                    $term = '';
                    break;
                case 'institutioncode':
                    $term = '';
                    break;
                case 'lifestage':
                    $term = 'http://rs.tdwg.org/dwc/terms/lifeStage';
                    break;
                case 'modified':
                    $term = 'http://purl.org/dc/terms/modified';
                    break;
                case 'occurrenceremarks':
                    $term = 'http://rs.tdwg.org/dwc/terms/occurrenceRemarks';
                    break;
                case 'ownerinstitutioncode':
                    $term = 'http://rs.tdwg.org/dwc/terms/ownerInstitutionCode';
                    break;
                case 'recordnumber':
                    $term = 'http://rs.tdwg.org/dwc/terms/institutionCode';
                    break;
                case 'rights':
                    $term = 'http://purl.org/dc/terms/rights';
                    break;
                case 'rightsholder':
                    $term = 'http://purl.org/dc/terms/rightsHolder';
                    break;
                case 'sex':
                    $term = 'http://rs.tdwg.org/dwc/terms/sex';
                    break;
                case 'sourcefileid':
                    $term = '';
                    break;
                case 'collectionid':
                    $term = '';
                    break;
                case 'geologicalcontextid':
                    $term = 'http://rs.tdwg.org/dwc/terms/geologicalContextID';
                    break;
                case 'eventid':
                    $term = 'http://rs.tdwg.org/dwc/terms/eventID';
                    break;
            }
            if ($term !='') {
                $node = $dwc->createElement('field');
                $node->setAttribute('index', $compt);
                $node->setAttribute('term', $term);
                $coreNode->appendChild($node) ;
                $compt++;
            }
        }
        return $root;
    }
    
    private function getXmlStratigraphy(\DOMDocument $dwc, $root, $keys) {
        $coreNode = $dwc->createElement('extension') ;
        $this->setCsvParameterNode($coreNode, 'http://rs.tdwg.org/dwc/terms/GeologicalContext');
        $root->appendChild($coreNode) ;
        $compt=0;
        $string='';
        foreach ($keys as $key=>$value) {
            $string.="case '$value':\n\t\$term = '';\n\tbreak;\n";
            $term='';
            switch ($value) {
                case 'geologicalcontextid':
                    $node = $dwc->createElement('id');
                    $node->setAttribute('index', $key);
                    $coreNode->appendChild($node) ;
                    $term = 'http://rs.tdwg.org/dwc/terms/geologicalContextID';
                    break;
                case 'bed':
                    $term = 'http://rs.tdwg.org/dwc/terms/bed';
                    break;
                case 'earliestageorloweststage':
                    $term = 'http://rs.tdwg.org/dwc/terms/earliestAgeOrLowestStage';
                    break;
                case 'earliesteonorlowesteonothem':
                    $term = 'http://rs.tdwg.org/dwc/terms/earliestEonOrLowestEonothem';
                    break;
                case 'earliestepochorlowestseries':
                    $term = 'http://rs.tdwg.org/dwc/terms/earliestEpochOrLowestSeries';
                    break;
                case 'earliesteraorlowesterathem':
                    $term = 'http://rs.tdwg.org/dwc/terms/earliestEraOrLowestErathem';
                    break;
                case 'earliestperiodorlowestsystem':
                    $term = 'http://rs.tdwg.org/dwc/terms/earliestPeriodOrLowestSystem';
                    break;
                case 'formation':
                    $term = 'http://rs.tdwg.org/dwc/terms/formation';
                    break;
                case 'group_':
                    $term = 'http://rs.tdwg.org/dwc/terms/group';
                    break;
                case 'highestbiostratigraphiczone':
                    $term = 'http://rs.tdwg.org/dwc/terms/highestBiostratigraphicZone';
                    break;
                case 'latestageorhigheststage':
                    $term = 'http://rs.tdwg.org/dwc/terms/latestAgeOrHighestStage';
                    break;
                case 'latesteonorhighesteonothem':
                    $term = 'http://rs.tdwg.org/dwc/terms/latestEonOrHighestEonothem';
                    break;
                case 'latestepochorhighestseries':
                    $term = 'http://rs.tdwg.org/dwc/terms/latestEpochOrHighestSeries';
                    break;
                case 'latesteraorhighesterathem':
                    $term = 'http://rs.tdwg.org/dwc/terms/latestEraOrHighestErathem';
                    break;
                case 'latestperiodorhighestsystem':
                    $term = 'http://rs.tdwg.org/dwc/terms/latestPeriodOrHighestSystem';
                    break;
                case 'lowestbiostratigraphiczone':
                    $term = 'http://rs.tdwg.org/dwc/terms/lowestBiostratigraphicZone';
                    break;
                case 'member':
                    $term = 'http://rs.tdwg.org/dwc/terms/member';
                    break;
                case 'sourcefileid':
                    $term = '';
                    break;
            }
            if ($term !='') {
                $node = $dwc->createElement('field');
                $node->setAttribute('index', $compt);
                $node->setAttribute('term', $term);
                $coreNode->appendChild($node) ;
                $compt++;
            }
        }
        //echo(sprintf('<pre>%s</pre>',$string));
        return $root;
    }
    private function getXmlRecolte(\DOMDocument $dwc, $root, $keys) {
        $coreNode = $dwc->createElement('extension') ;
        $this->setCsvParameterNode($coreNode, 'http://rs.tdwg.org/dwc/terms/Event');
        $root->appendChild($coreNode) ;
        $compt=0;
        $string='';
        foreach ($keys as $key=>$value) {
            $string.="case '$value':\n\t\$term = '';\n\tbreak;\n";
            $term='';
            switch ($value) {
                case 'eventid':
                    $node = $dwc->createElement('id');
                    $node->setAttribute('index', $key);
                    $coreNode->appendChild($node) ;
                    $term = 'http://rs.tdwg.org/dwc/terms/eventID';
                    break;
                case 'decade':
                    $term = '';
                    break;
                case 'eday':
                    $term = 'http://rs.tdwg.org/dwc/terms/day';
                    break;
                case 'emonth':
                    $term = 'http://rs.tdwg.org/dwc/terms/month';
                    break;
                case 'eventdate':
                    $term = 'http://rs.tdwg.org/dwc/terms/eventDate';
                    break;
                case 'eventremarks':
                    $term = 'http://rs.tdwg.org/dwc/terms/eventRemarks';
                    break;
                case 'eyear':
                    $term = 'http://rs.tdwg.org/dwc/terms/year';
                    break;
                case 'fieldnotes':
                    $term = 'http://rs.tdwg.org/dwc/terms/fieldNotes';
                    break;
                case 'fieldnumber':
                    $term = 'http://rs.tdwg.org/dwc/terms/fieldNumber';
                    break;
                case 'habitat':
                    $term = 'http://rs.tdwg.org/dwc/terms/habitat';
                    break;
                case 'recordedby':
                    $term = 'http://rs.tdwg.org/dwc/terms/recordedBy';
                    break;
                case 'sday':
                    $term = '';
                    break;
                case 'smonth':
                    $term = '';
                    break;
                case 'sourcefileid':
                    $term = '';
                    break;
                case 'syear':
                    $term = '';
                    break;
                case 'verbatimeventdate':
                    $term = 'http://rs.tdwg.org/dwc/terms/verbatimEventDate';
                    break;
                case 'locationid':
                    $term = 'http://rs.tdwg.org/dwc/terms/locationID';
                    break;
            }
            if ($term !='') {
                $node = $dwc->createElement('field');
                $node->setAttribute('index', $compt);
                $node->setAttribute('term', $term);
                $coreNode->appendChild($node) ;
                $compt++;
            }
        }
        //echo(sprintf('<pre>%s</pre>',$string));
        return $root;
    }
    
    private function getXmlLocalisation(\DOMDocument $dwc, $root, $keys) {
        $coreNode = $dwc->createElement('extension') ;
        $this->setCsvParameterNode($coreNode, 'http://purl.org/dc/terms/Location');
        $root->appendChild($coreNode) ;
        $compt=0;
        $string='';
        foreach ($keys as $key=>$value) {
            $string.="case '$value':\n\t\$term = '';\n\tbreak;\n";
            $term='';
            switch ($value) {
                case 'locationid':
                    $node = $dwc->createElement('id');
                    $node->setAttribute('index', $key);
                    $coreNode->appendChild($node) ;
                    $term = 'http://rs.tdwg.org/dwc/terms/locationID';
                    break;
                case 'averagealtituderounded':
                    $term = '';
                    break;
                case 'continent':
                    $term = 'http://rs.tdwg.org/dwc/terms/continent';
                    break;
                case 'country':
                    $term = 'http://rs.tdwg.org/dwc/terms/country';
                    break;
                case 'countrycode':
                    $term = 'http://rs.tdwg.org/dwc/terms/countryCode';
                    break;
                case 'county':
                    $term = 'http://rs.tdwg.org/dwc/terms/county';
                    break;
                case 'decimallatitude':
                    $term = 'http://rs.tdwg.org/dwc/terms/decimalLatitude';
                    break;
                case 'decimallongitude':
                    $term = 'http://rs.tdwg.org/dwc/terms/decimalLongitude';
                    break;
                case 'geodeticdatum':
                    $term = 'http://rs.tdwg.org/dwc/terms/geodeticDatum';
                    break;
                case 'georeferencesources':
                    $term = 'http://rs.tdwg.org/dwc/terms/georeferenceSources';
                    break;
                case 'hascoordinates':
                    $term = '';
                    break;
                case 'locality':
                    $term = 'http://rs.tdwg.org/dwc/terms/locality';
                    break;
                case 'locationremarks':
                    $term = 'http://rs.tdwg.org/dwc/terms/locationRemarks';
                    break;
                case 'maximumdepthinmeters':
                    $term = 'http://rs.tdwg.org/dwc/terms/maximumDepthInMeters';
                    break;
                case 'maximumelevationinmeters':
                    $term = 'http://rs.tdwg.org/dwc/terms/maximumElevationInMeters';
                    break;
                case 'minimumdepthinmeters':
                    $term = 'http://rs.tdwg.org/dwc/terms/minimumDepthInMeters';
                    break;
                case 'minimumelevationinmeters':
                    $term = 'http://rs.tdwg.org/dwc/terms/minimumElevationInMeters';
                    break;
                case 'municipality':
                    $term = 'http://rs.tdwg.org/dwc/terms/municipality';
                    break;
                case 'sourcefileid':
                    $term = '';
                    break;
                case 'stateprovince':
                    $term = 'http://rs.tdwg.org/dwc/terms/stateProvince';
                    break;
                case 'verbatimcountry':
                    $term = '';
                    break;
                case 'verbatimelevation':
                    $term = 'http://rs.tdwg.org/dwc/terms/verbatimElevation';
                    break;
                case 'verbatimlocality':
                    $term = 'http://rs.tdwg.org/dwc/terms/verbatimLocality';
                    break;
            }
            if ($term !='') {
                $node = $dwc->createElement('field');
                $node->setAttribute('index', $compt);
                $node->setAttribute('term', $term);
                $coreNode->appendChild($node) ;
                $compt++;
            }
        }
        //echo(sprintf('<pre>%s</pre>',$string));
        return $root;
    }
    private function getXmlDetermination(\DOMDocument $dwc, $root, $keys) {
        $coreNode = $dwc->createElement('extension') ;
        $this->setCsvParameterNode($coreNode, 'http://rs.tdwg.org/dwc/terms/Determination');
        $root->appendChild($coreNode) ;
        $compt=0;
        $string='';
        foreach ($keys as $key=>$value) {
            $string.="case '$value':\n\t\$term = '';\n\tbreak;\n";
            $term='';
            switch ($value) {
                case 'identificationid':
                    $node = $dwc->createElement('id');
                    $node->setAttribute('index', $key);
                    $coreNode->appendChild($node);
                    $term = 'http://rs.tdwg.org/dwc/terms/identificationID';
                    break;
                case 'created':
                    $term = 'http://purl.org/dc/terms/created';
                    break;
                case 'dateidentified':
                    $term = 'http://rs.tdwg.org/dwc/terms/dateIdentified';
                    break;
                case 'dwcaidentificationid':
                    $term = '';
                    break;
                case 'identificationqualifier':
                    $term = 'http://rs.tdwg.org/dwc/terms/identificationQualifier';
                    break;
                case 'identificationreferences':
                    $term = 'http://rs.tdwg.org/dwc/terms/identificationReferences';
                    break;
                case 'identificationremarks':
                    $term = 'http://rs.tdwg.org/dwc/terms/identificationRemarks';
                    break;
                case 'identificationverifstatus':
                    $term = 'http://rs.tdwg.org/dwc/terms/typeStatus';
                    break;
                case 'identifiedby':
                    $term = 'http://rs.tdwg.org/dwc/terms/identifiedBy';
                    break;
                case 'modified':
                    $term = 'http://purl.org/dc/terms/modified';
                    break;
                case 'sourcefileid':
                    $term = '';
                    break;
                case 'typestatus':
                    $term = 'http://rs.tdwg.org/dwc/terms/typeStatus';
                    break;
                case 'occurrenceid':
                    $term = 'http://rs.tdwg.org/dwc/terms/occurrenceID';
                    break;
                case 'taxonid':
                    $term = 'http://rs.tdwg.org/dwc/terms/taxonID';
                    break;
            }
            if ($term !='') {
                $node = $dwc->createElement('field');
                $node->setAttribute('index', $compt);
                $node->setAttribute('term', $term);
                $coreNode->appendChild($node) ;
                $compt++;
            }
        }
        //echo(sprintf('<pre>%s</pre>',$string));
        return $root;
    }
    private function getXmlTaxon(\DOMDocument $dwc, $root, $keys) {
        $coreNode = $dwc->createElement('extension') ;
        $this->setCsvParameterNode($coreNode, 'http://rs.tdwg.org/dwc/terms/Taxon');
        $root->appendChild($coreNode) ;
        $compt=0;
        $string='';
        foreach ($keys as $key=>$value) {
            $string.="case '$value':\n\t\$term = '';\n\tbreak;\n";
            $term='';
            switch ($value) {
                case 'taxonid':
                    $node = $dwc->createElement('id');
                    $node->setAttribute('index', $key);
                    $coreNode->appendChild($node);
                    $term = 'http://rs.tdwg.org/dwc/terms/taxonID';
                    break;
                case 'acceptednameusage':
                    $term = 'http://rs.tdwg.org/dwc/terms/acceptedNameUsage';
                    break;
                case 'class_':
                    $term = 'http://rs.tdwg.org/dwc/terms/class';
                    break;
                case 'created':
                    $term = 'http://purl.org/dc/terms/created';
                    break;
                case 'dwcataxonid':
                    $term = '';
                    break;
                case 'family':
                    $term = 'http://rs.tdwg.org/dwc/terms/family';
                    break;
                case 'genus':
                    $term = 'http://rs.tdwg.org/dwc/terms/genus';
                    break;
                case 'higherclassification':
                    $term = 'http://rs.tdwg.org/dwc/terms/higherClassification';
                    break;
                case 'infraspecificepithet':
                    $term = 'http://rs.tdwg.org/dwc/terms/infraspecificEpithet';
                    break;
                case 'kingdom':
                    $term = 'http://rs.tdwg.org/dwc/terms/kingdom';
                    break;
                case 'modified':
                    $term = 'http://purl.org/dc/terms/modified';
                    break;
                case 'nameaccordingto':
                    $term = 'http://rs.tdwg.org/dwc/terms/nameAccordingTo';
                    break;
                case 'namepublishedin':
                    $term = 'http://rs.tdwg.org/dwc/terms/namePublishedIn';
                    break;
                case 'namepublishedinyear':
                    $term = 'http://rs.tdwg.org/dwc/terms/namePublishedInYear';
                    break;
                case 'nomenclaturalcode':
                    $term = 'http://rs.tdwg.org/dwc/terms/nomenclaturalCode';
                    break;
                case 'nomenclaturalstatus':
                    $term = 'http://rs.tdwg.org/dwc/terms/taxonomicStatus';
                    break;
                case 'order_':
                    $term = 'http://rs.tdwg.org/dwc/terms/order';
                    break;
                case 'originalnameusage':
                    $term = 'http://rs.tdwg.org/dwc/terms/originalNameUsage';
                    break;
                case 'parentnameusage':
                    $term = 'http://rs.tdwg.org/dwc/terms/parentNameUsage';
                    break;
                case 'phylum':
                    $term = 'http://rs.tdwg.org/dwc/terms/phylum';
                    break;
                case 'scientificname':
                    $term = 'http://rs.tdwg.org/dwc/terms/scientificName';
                    break;
                case 'scientificnameauthorship':
                    $term = 'http://rs.tdwg.org/dwc/terms/scientificNameAuthorship';
                    break;
                case 'sourcefileid':
                    $term = '';
                    break;
                case 'specificepithet':
                    $term = 'http://rs.tdwg.org/dwc/terms/specificEpithet';
                    break;
                case 'subgenus':
                    $term = 'http://rs.tdwg.org/dwc/terms/subgenus';
                    break;
                case 'taxonomicstatus':
                    $term = 'http://rs.tdwg.org/dwc/terms/taxonomicStatus';
                    break;
                case 'taxonrank':
                    $term = 'http://rs.tdwg.org/dwc/terms/taxonRank';
                    break;
                case 'taxonremarks':
                    $term = 'http://rs.tdwg.org/dwc/terms/taxonRemarks';
                    break;
                case 'verbatimtaxonrank':
                    $term = 'http://rs.tdwg.org/dwc/terms/verbatimTaxonRank';
                    break;
                case 'vernacularname':
                    $term = 'http://rs.tdwg.org/dwc/terms/vernacularName';
                    break;
            }
            if ($term !='') {
                $node = $dwc->createElement('field');
                $node->setAttribute('index', $compt);
                $node->setAttribute('term', $term);
                $coreNode->appendChild($node) ;
                $compt++;
            }
        }
        //echo(sprintf('<pre>%s</pre>',$string));
        return $root;
    }
    private function getXmlBibliography(\DOMDocument $dwc, $root, $keys) {
        $coreNode = $dwc->createElement('extension') ;
        $this->setCsvParameterNode($coreNode, 'http://purl.org/dc/terms/');
        $root->appendChild($coreNode) ;
        $compt=0;
        $string='';
        foreach ($keys as $key=>$value) {
            $string.="case '$value':\n\t\$term = '';\n\tbreak;\n";
            $term='';
            switch ($value) {
                case 'referenceid':
                    
                    $term = '';
                    break;
                case 'bibliographiccitation':
                    $term = 'http://purl.org/dc/terms/bibliographicCitation';
                    break;
                case 'creator':
                    $term = 'http://purl.org/dc/terms/creator';
                    break;
                case 'date_publication':
                    $term = '';
                    break;
                case 'description':
                    $term = '';
                    break;
                case 'identifier':
                    $term = '';
                    break;
                case 'language':
                    $term = 'http://purl.org/dc/terms/language';
                    break;
                case 'rights':
                    $term = 'http://purl.org/dc/terms/rights';
                    break;
                case 'source':
                    $term = '';
                    break;
                case 'sourcefileid':
                    $term = '';
                    break;
                case 'subject':
                    $term = '';
                    break;
                case 'taxonremarks':
                    $term = 'http://rs.tdwg.org/dwc/terms/taxonRemarks';
                    break;
                case 'title':
                    $term = '';
                    break;
                case 'type':
                    $term = '';
                    break;
                case 'occurrenceid':
                    $term = 'http://rs.gbif.org/terms/1.0/gbifID';
                    break;
            }
            if ($term !='') {
                $node = $dwc->createElement('field');
                $node->setAttribute('index', $compt);
                $node->setAttribute('term', $term);
                $coreNode->appendChild($node) ;
                $compt++;
            }
        }
        //echo(sprintf('<pre>%s</pre>',$string));
        return $root;
    }
}