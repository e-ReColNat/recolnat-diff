<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of TaxonExporter
 *
 * @author tpateffoz
 */
class TaxonExporter extends AbstractEntityExporter
{

    protected function getExportProperties($fieldName)
    {
        $term = '';
        switch ($fieldName) {
            case 'taxonid':
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
        return $term;
    }

    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/Taxon';
    }
    public function getIdFieldName()
    {
        return 'taxonid';
    }
}
