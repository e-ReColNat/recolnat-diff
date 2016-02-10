<?php

namespace AppBundle\Business\Exporter\Entity;

/**
 * Description of AbstractEntityExporter
 *
 * @author tpateffoz
 */
abstract class AbstractEntityExporter
{

    /**
     *
     * @var \AppBundle\Entity\Specimen
     */
    public $entity;
    public $arrayExportTerm = [];
    private $className;

    public function __construct()
    {
        $fullClassName = explode('\\', get_called_class());
        $fullClassName = end($fullClassName);
        $this->className = str_replace('Exporter', '', $fullClassName);
        $entityConstructor = '\\AppBundle\\Entity\\'.ucfirst($this->className);
        $this->entity = new $entityConstructor;
        $this->setExportTerm();
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function getKeysEntity()
    {
        return array_keys($this->getExportProperties());
    }

    public function exportToCsv($fieldName)
    {
        return is_null($this->getExportProperty($fieldName)) ? false : true;
    }

    public function getXmlTerm($fieldName)
    {
        return $this->getExportProperty($fieldName);
    }

    public function getExportProperty($fieldName)
    {
        if (array_key_exists($fieldName, $this->getExportProperties())) {
            return $this->getExportProperties()[$fieldName];
        }
        return null;
    }
    public function getExportProperties()
    {
        return $this->arrayExportTerm;
    }


    abstract public function getNameSpace();

    abstract public function getIdFieldName();

    abstract public function getCoreIdFieldName();

    abstract public function setExportTerm();
}
