<?php

namespace AppBundle\Business\Exporter;

/**
 * Description of Exporter
 *
 * @author tpateffoz
 */
abstract class AbstractExporter
{
    public $datas = [];
    public function __construct($datas) {
        $this->datas = $datas;
    }
    public function getFile() {
        
    }
    
}
