<?php

namespace AppBundle\Business\Exporter;
use AppBundle\Manager\GenericEntityManager;
/**
 * Description of ExporterFactory
 *
 * @author tpateffoz
 */
class ExporterFactory
{
    /* @var $genericEntityManager \AppBundle\Manager\GenericEntityManager */
    public $genericEntityManager ;
    public function __construct(GenericEntityManager $genericEntityManager)  
    {
        $this->genericEntityManager = $genericEntityManager ;
    }
    
    public function getDwcExporter($datas, $exportPath) 
    {
        return new DwcExporter($datas, $exportPath, $this->genericEntityManager);
    }
    public function getCsvExporter($datas, $exportPath) 
    {
        return new CsvExporter($datas, $exportPath, $this->genericEntityManager);
    }
}
