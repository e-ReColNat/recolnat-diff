<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of TaxonExporter
 *
 * @author tpateffoz
 */
class TaxonExporter extends AbstractEntityExporter
{
    public function setExportTerm()
        {
        $this->arrayExportTerm = [
            'acceptednameusage' => 'http://rs.tdwg.org/dwc/terms/acceptedNameUsage',
            'class' => 'http://rs.tdwg.org/dwc/terms/class',
            'family' => 'http://rs.tdwg.org/dwc/terms/family',
            'genus' => 'http://rs.tdwg.org/dwc/terms/genus',
            'higherclassification' => 'http://rs.tdwg.org/dwc/terms/higherClassification',
            'infraspecificepithet' => 'http://rs.tdwg.org/dwc/terms/infraspecificEpithet',
            'kingdom' => 'http://rs.tdwg.org/dwc/terms/kingdom',
            'modified' => 'http://purl.org/dc/terms/modified',
            'nameaccordingto' => 'http://rs.tdwg.org/dwc/terms/nameAccordingTo',
            'namepublishedin' => 'http://rs.tdwg.org/dwc/terms/namePublishedIn',
            'namepublishedinyear' => 'http://rs.tdwg.org/dwc/terms/namePublishedInYear',
            'nomenclaturalcode' => 'http://rs.tdwg.org/dwc/terms/nomenclaturalCode',
            'nomenclaturalstatus' => 'http://rs.tdwg.org/dwc/terms/taxonomicStatus',
            'order' => 'http://rs.tdwg.org/dwc/terms/order',
            'originalnameusage' => 'http://rs.tdwg.org/dwc/terms/originalNameUsage',
            'parentnameusage' => 'http://rs.tdwg.org/dwc/terms/parentNameUsage',
            'phylum' => 'http://rs.tdwg.org/dwc/terms/phylum',
            'scientificname' => 'http://rs.tdwg.org/dwc/terms/scientificName',
            'scientificnameauthorship' => 'http://rs.tdwg.org/dwc/terms/scientificNameAuthorship',
            'specificepithet' => 'http://rs.tdwg.org/dwc/terms/specificEpithet',
            'subgenus' => 'http://rs.tdwg.org/dwc/terms/subgenus',
            'taxonomicstatus' => 'http://rs.tdwg.org/dwc/terms/taxonomicStatus',
            'taxonrank' => 'http://rs.tdwg.org/dwc/terms/taxonRank',
            'taxonremarks' => 'http://rs.tdwg.org/dwc/terms/taxonRemarks',
            'verbatimtaxonrank' => 'http://rs.tdwg.org/dwc/terms/verbatimTaxonRank',
            'vernacularname' => 'http://rs.tdwg.org/dwc/terms/vernacularName',
        ];
    }

    public function getNameSpace()
    {
        return 'http://rs.tdwg.org/dwc/terms/Taxon';
    }
    public function getIdFieldName()
    {
        return 'taxonid';
    }
    
    public function getCoreIdFieldName()
    {
        return 'identificationid';
    }
}
