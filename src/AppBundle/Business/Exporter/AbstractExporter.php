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
    public $exportPath;
    public function __construct($datas, $exportPath) {
        $this->datas = $datas;
        $this->exportPath = $exportPath;
    }
    public function getExportDirPath() {
        return realpath($this->exportPath);
    }
    abstract public function generate();
    
}
