<?php
namespace Recolnat\DarwinCoreBundle\Component\Extension;

use Recolnat\DarwinCoreBundle\Exception\DarwinCoreException;
use Symfony\Component\Config\Definition\Exception\Exception;
abstract class Extension
{
    const  ALLOWED_EXTENSION = array(
        'Identification',
        'Multimedia',
        'Location',
        'Occurrence',
        'Taxon',
        'Event',
        'GeologicalContext',
        'ResourceRelationship',
        'MeasurementOrFact'
    );
    /**
     * Is core file ?
     * @var boolean
     */
    protected $core = FALSE;
    
    /**
     * @var \SplFileObject
     */
    protected $file;
    
    /**
     * @var array
     */
    //protected $files = array();
    /*
     * @var string
     */
    protected $fieldsTerminatedBy=',';
    
    /*
     * @var string
     */
    protected $fieldsEnclosedBy='"';
    
    /*
     * @var string
     */
    protected $linesTerminatedBy="\n";
    
    /*
     * @var string
     */
    protected $encoding='UTF-8';
    
    /*
     * @var string
     */
    protected $ignoreHeaderLines=0;
    
    /*
     * @var string
     */
    protected $dateFormat='YYYY-MM-DD';
    
    /*
     * @var string
     * 
     *   Simple Darwin Record
     *       http://rs.tdwg.org/dwc/xsd/simpledarwincore/SimpleDarwinRecord
     *   Occurrence
     *       http://rs.tdwg.org/dwc/terms/Occurrence
     *   Event
     *       http://rs.tdwg.org/dwc/terms/Event
     *   Location
     *       http://purl.org/dc/terms/Location
     *   GeologicalContext
     *       http://purl.org/dc/terms/GeologicalContext
     *   Identification
     *       http://rs.tdwg.org/dwc/terms/Identification
     *   Taxon
     *       http://rs.tdwg.org/dwc/terms/Taxon
     *   ResourceRelationship
     *       http://rs.tdwg.org/dwc/terms/ResourceRelationship
     *   MeasurementOrFact
     *       http://rs.tdwg.org/dwc/terms/MeasurementOrFact 
     */
    protected $rowType='http://rs.tdwg.org/dwc/xsd/simpledarwincore/SimpleDarwinRecord';
    
    /**
     * Row Type shorter eg : http://rs.tdwg.org/dwc/terms/Occurrence -> Occurrence
     * @var String
     */
    protected $shortType;
    /**
     * @var int
     */
    protected $id;
    
    /**
     * @var int
     */
    protected $coreId = null;
    
    /**
     * @var array
     */
    public $fields = array();
    
    /**
     * @var array
     */
    public $indexes = array();
    
    /**
     * 
     * @var ArrayObject
     */
    private $linkedExtensions;
    /**
     * @var array
     */
    public $data;
    /**
     * @var string
     */
    private $path;
    private $boolLinkedParse=FALSE;


    public function setIndexData(\DOMElement $field) 
    {
        $indexData=array();
        $term = explode('/', $field->getAttribute('term'));
        $indexData['shortTerm'] = end($term);
        $indexData['vocabulary'] = $field->getAttribute('vocabulary') ? $field->getAttribute('vocabulary') : '';
        $indexData['default'] = $field->getAttribute('default') ? $field->getAttribute('default') : '';
        $indexData['term'] = $field->getAttribute('term');
        return $indexData;
    }
    
    private function translateQuoted($text)
    {
        return str_replace(array("\\t", "\\n", "\\r"), array("\t", "\n", "\r"), $text);
    }
    
  
    public function getRecord($id) {
        if (array_key_exists($id, $this->data)) {
            return $this->data[$id];
        }
        else {
            throw new \Exception(sprintf('can\'t find record #%s', $id));
        }
    }
    public function isCore()
    {
        return $this->core;
    }

    public function setCore($core)
    {
        $this->core = $core;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(\SplFileObject $file)
    {
        $file->setFlags(
            \SplFileObject::READ_CSV | 
            \SplFileObject::READ_AHEAD | 
            \SplFileObject::SKIP_EMPTY | 
            \SplFileObject::DROP_NEW_LINE);
        $this->file = $file;
        return $this;
    }
/*
    public function getFiles()
    {
        return $this->files;
    }

    public function setFiles(array $files)
    {
        $this->files = $files;
        return $this;
    }
*/
    public function getFieldsTerminatedBy()
    {
        return $this->translateQuoted($this->fieldsTerminatedBy);
    }

    public function setFieldsTerminatedBy($fieldsTerminatedBy)
    {
        if (!empty($fieldsTerminatedBy)) {
            $this->fieldsTerminatedBy = $fieldsTerminatedBy;
        }
        return $this;
    }

    public function getFieldsEnclosedBy()
    {
        return $this->translateQuoted($this->fieldsEnclosedBy);
    }

    public function setFieldsEnclosedBy($fieldsEnclosedBy)
    {
        if (!empty($fieldsEnclosedBy)) {
            $this->fieldsEnclosedBy = $fieldsEnclosedBy;
        }
        return $this;
    }

    public function getLinesTerminatedBy()
    {
        return $this->translateQuoted($this->linesTerminatedBy);
    }

    public function setLinesTerminatedBy($linesTerminatedBy)
    {
        if (!empty($linesTerminatedBy)) {
            $this->linesTerminatedBy = $linesTerminatedBy;
        }
        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setEncoding($encoding)
    {
        if (!empty($encoding)) {
            $this->encoding = $encoding;
        }
        return $this;
    }

    public function getIgnoreHeaderLines()
    {
        return $this->ignoreHeaderLines;
    }

    public function setIgnoreHeaderLines($ignoreHeaderLines)
    {
        if (!empty($ignoreHeaderLines)) {
            $this->ignoreHeaderLines = $ignoreHeaderLines;
        }
        return $this;
    }

    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    public function setDateFormat($dateFormat)
    {
        if (!empty($dateFormat)) {
            $this->dateFormat = $dateFormat;
        }
        return $this;
    }

    public function getRowType()
    {
        return $this->rowType;
    }

    public function setRowType($rowType)
    {
        $this->rowType = $rowType;
        $explodedType = explode('/', $rowType);
        $this->shortType = end($explodedType);
        if (!in_array($this->shortType, self::ALLOWED_EXTENSION)) {
            throw new \Exception(sprintf('The extension "%s" is not (yet) allowed', $this->shortType));
        }
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getCoreId()
    {
        return $this->coreId;
    }

    public function setCoreId($coreId)
    {
        $this->coreId = $coreId;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * 
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }
    /**
     * @param int $key
     * @return array
     */
    public function getField($key)
    {
        if (array_key_exists((int) $key, $this->getFields())) {
            return $this->getFields()[$key] ;
        }
        return null;
    }
    /**
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }
    
    public function getData($id = NULL, $field = NULL)
    {
        if (is_null($id) && is_null($field)) {
            return $this->data; 
        }
        if (!is_null($id) && is_null($field)) {
            return $this->data[$id]; 
        }
        if (is_int($field)) {
            $field = array_flip($this->getIndexes())[$field] ;
        }
        if (isset($this->indexes[$field])) {
            return isset($this->data[$id][$this->indexes[$field]]) ? $this->data[$id][$this->indexes[$field]] : NULL ;
        }
    }
     
    

     public function getRowData($id) {
        if (array_key_exists($id, $this->data)) {
            return $this->data[$id];
        }
        return null;
    }
    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
    
    public function toXml($withExtensions = FALSE)
    {
        $dwc = new \DOMDocument('1.0', 'UTF-8');
        $root = $dwc->createElementNS(
            'http://rs.tdwg.org/dwc/dwcrecord/',
            'dwr:DarwinRecordSet'
            );
        $dwc->appendChild($root) ;
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation', 'http://rs.tdwg.org/dwc/dwcrecord/ http://purl.org/dc/terms/ http://rs.tdwg.org/dwc/xsd/tdwg_dwc_classes.xsd');
        foreach ($this->getData() as $class) {
            $node = $dwc->importNode($class->toXml(), true);
            if ($withExtensions && $this->getLinkedExtensions()->count()>0) {
                foreach ($this->getLinkedExtensions() as $linkedExtension) {
                    /* @var Extension $linkedExtension */
                    foreach ($linkedExtension->getData() as $linkedData) {
                        if ($linkedData->getData('coreId') == $class->getData('id')) {
                            $node->appendChild($dwc->importNode($linkedData->toXml(), true));
                        }
                    }
                }
            }
            $root->appendChild($node) ;
        }
        return $dwc->saveXML($root);
    }

    /**
     * @return ArrayObject
     */
    public function getLinkedExtensions()
    {
        return $this->linkedExtensions;
    }

    public function setLinkedExtensions(\ArrayObject $linkedExtensions)
    {
        $this->linkedExtensions = $linkedExtensions;
        return $this;
    }
    public function getShortType() {
        return $this->shortType;
    }
    /**
     * @return array
     */
    public function getDatasWithIndexes() {
        $datasWithIndexes = array();
        foreach ($this->data as $id=>$row) {
            foreach ($this->getIndexes() as $key => $value) {
                if (isset($row[$value])) {
                    $datasWithIndexes[$id][$key] = $row[$value];
                }
                else {
                    $datasWithIndexes[$id][$key] = null;
                }
            }
        }
        return $datasWithIndexes;
    }
    /**
     * @return array
     */
    public function getRowWithIndexes($id) {
        $datasWithIndexes = array();
        if (isset($this->data[$id])) {
            $row = $this->data[$id];
            foreach ($this->getIndexes() as $key => $value) {
                if (isset($row[$value])) {
                    $datasWithIndexes[$key] = $row[$value];
                }
                else {
                    $datasWithIndexes[$key] = null;
                }
            }
        }
        return $datasWithIndexes;
    }
    /**
     * @return int | null
     * @param string $name
     */
    public function getNumericIndex($name) 
    {
        if (array_key_exists($name, $this->getIndexes())) {
            return $this->getIndexes()[$name];
        }
        return null;
    }
    /**
     * 
     * @param string $name
     * @return mixed|string
     */
    public function __get($name) 
    {
        return $this->getData($name);
    }
    
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    
    public function __isset($name) {
        return array_key_exists($name, $this->getIndexes());
    }

    private function isDateValid($str) 
    {
          if (!is_string($str)) {
             return false;
          }
        
          $stamp = strtotime($str); 
        
          if (!is_numeric($stamp)) {
             return false; 
          }
        
          if ( checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp)) ) { 
             return true; 
          } 
          return false; 
    } 
}