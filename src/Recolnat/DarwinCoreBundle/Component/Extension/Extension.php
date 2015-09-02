<?php
namespace Recolnat\DarwinCoreBundle\Component\Extension;

use Recolnat\DarwinCoreBundle\Exception\DarwinCoreException;
use Symfony\Component\Config\Definition\Exception\Exception;
class Extension
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
    protected $fields = array();
    
    /**
     * @var array
     */
    protected $indexes = array();
    
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
    
    public function __construct(\DOMNode $node, $path)
    {
        $this->data = array();
        $this->linkedExtensions = new \ArrayObject();
        $this->setPath($path);
        $this->collectData($node);
    }
    private function collectData(\DOMNode $node)
    {
        $filesNode = $node->getElementsByTagName('files') ;
        if (count($filesNode) == 0) {
            throw new \Exception('There is no files node');
        }
        if ($node->tagName == 'core') {
            $this->setCore(TRUE);
        }
        /**
         * @var $file \DOMElement
         */
        foreach ($filesNode as $file) {
            $tmpFilePath=sprintf($this->path.'%s', trim($file->nodeValue));
            if (!is_file($tmpFilePath)) {
                throw new DarwinCoreException(sprintf('Can\'t find the file : %s', $tmpFilePath));
            }
            $this->setFile(new \SplFileObject($tmpFilePath));
        }
        $this->setEncoding($node->getAttribute('encoding'));
        $this->setRowType($node->getAttribute('rowType'));
        $this->setFieldsTerminatedBy($node->getAttribute('fieldsTerminatedBy'));
        $this->setLinesTerminatedBy($node->getAttribute('linesTerminatedBy'));
        $this->setFieldsEnclosedBy($node->getAttribute('fieldsEnclosedBy'));
        $this->setIgnoreHeaderLines($node->getAttribute('ignoreHeaderLines'));
        $this->setDateFormat($node->getAttribute('dateFormat'));
        
        $this->extractFields($node);
        $this->parseCsvFile();
    }
    /**
     * Parse index fields
     * @param \DOMNode $node
     * @return array
     */
    private function extractFields(\DOMNode $node)
    {
        $fields=array();
        if ($node->tagName == 'core') {
            $this->id = (int) $node->getElementsByTagName('id')->item(0)->getAttribute('index');
            $fields[$this->id]['shortTerm'] = 'id' ;
            $this->indexes[$fields[$this->id]['shortTerm']] = $this->id;
        }
        else {
            $this->coreId = (int) $node->getElementsByTagName('coreid')->item(0)->getAttribute('index');
            $fields[$this->coreId]['shortTerm'] = 'coreId' ;
            $this->indexes[$fields[$this->coreId]['shortTerm']] = $this->coreId;
        }
        
        
        $fieldList = $node->getElementsByTagName('field');
        if (count($fieldList) > 0) {
            foreach ($fieldList as $field) {
                $key = (int) $field->getAttribute('index');
                $fields[$key] = $this->setIndexData($field);
                $this->indexes[$fields[$key]['shortTerm']] = $key;
            }
        }
        $this->fields = $fields;
    }

    private function setIndexData(\DOMElement $field) 
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
    
    private function parseCsvFile()
    {
        $className = __NAMESPACE__.'\\'.ucfirst($this->shortType);
        $rowCount=0;
        $this->file->setCsvControl($this->getFieldsTerminatedBy(), $this->getFieldsEnclosedBy());
        while(!$this->file->eof() && ($row = $this->file->fgetcsv()) && $row[0] !== null) {
            $rowCount++;
            if ($this->getIgnoreHeaderLines() && $rowCount == 1) {
                continue;
            }
            if ($this->isCore()) {
                $this->data[$row[$this->id]] = new $className($row, $this);
            }
            else {
                $this->data[$row[$this->coreId]] = new $className($row, $this);
            }
        }
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
        if (empty($rowType)) {
            throw new Exception('the attribute rowType is mandatory');
        }
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
    public function getData()
    {
        return $this->data; 
    }
    
    /*Buggy method */
     public function getRowData($id) {
        
        /*if (!is_null($this->coreId)) {
            return $this->data[$id];
        }
        else {
            
        }
        //|| $this->core*/
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
    function getShortType() {
        return $this->shortType;
    }
}