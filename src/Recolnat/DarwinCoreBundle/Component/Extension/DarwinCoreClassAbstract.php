<?php
namespace Recolnat\DarwinCoreBundle\Component\Extension;

abstract Class DarwinCoreClassAbstract
{
    /**
     * 
     * @var array
     */
    public $data = array();
    /**
     * @var Extension
     */
    private $extension;

    public function __construct($data, Extension $extension)
    {
        $this->extension = $extension;
        //$errorFormat='csv file %s is not well formated. Field #%s=>%s is missing.<br />';
        foreach ($this->getIndexes() as $value=>$key) {
            if (isset($data[$key])) {
                $this->data[$key] = $data[$key];
            }
            else {
                //echo sprintf($errorFormat, get_class($this), $key, $value);
            }
        }
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

    /**
     * 
     * @return \Recolnat\DarwinCoreBundle\Component\Extension\Extension
     */
    public function getExtension() 
    {
        return $this->extension;
    }
    /**
     * 
     * @return string|mixed
     */
    public function getCoreId()
    {
        return $this->getData('index');
    }

    /**
     * 
     * @return string|mixed
     */
    public function getId()
    {
        return $this->getData('identificationid');
    }
    /**
     * @return array
     */
    public function getDatas() 
    {
        return $this->data;    
    }
    /**
     * 
     * @param string $name
     * @return mixed|string
     */
    public function getData($name) 
    {
        if (array_key_exists($name, $this->getIndexes())) {
            if (stristr($name, 'date')) {
                if ($this->isDateValid($this->data[$this->getIndexes()[$name]]))
                {
                    try {
                        $date = new \DateTime($this->data[$this->getIndexes()[$name]]);
                        return $date->format('c');
                    } 
                    catch (Exception $e) {
                    }
                }
            }
            return $this->data[$this->getIndexes()[$name]];
        }
        return sprintf('%s not found', $name);
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
    public function getIndexes()
    {
        return $this->getExtension()->getIndexes();
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
    public function getFieldIndexes()
    {
        return $this->getExtension()->getFields();
    }
 
    /**
     * @return array
     */
    public function getDatasWithIndexes() {
        $datasWithIndexes = array();
        foreach ($this->getIndexes() as $key => $value) {
            $datasWithIndexes[$key] = $this->getData($key);
        }
        return $datasWithIndexes;
    }
    /**
     * @return \DOMElement
     */
    public function toXml()
    {
        $dwc = new \DOMDocument('1.0', 'UTF-8');
        $record = $dwc->createElementNS('http://rs.tdwg.org/dwc/terms/', 'dwc:'.$this->getDwcName());
        foreach($this->getDatasWithIndexes() as $name => $value) {
            if (!empty($value) && $name != 'id' && $name != 'coreId') {
                $node = $this->createXmlNode($dwc, $name, $value);
                $record->appendChild($node);
            }
        }
        return $record;
    }
    
    public function createXmlNode(\DOMDocument $dwc, $name, $value)
    {
        $node=null;
        $term = $this->getExtension()->getField($this->getNumericIndex($name))['term'] ;
        
        $textNode = $dwc->createTextNode($value) ;
        if (strstr($term, 'http://purl.org/dc/')) {
            $node = $dwc->createElementNS('http://purl.org/dc/terms/', 'dc:'.$name);
        }
        if (strstr($term, 'http://rs.tdwg.org/dwc/')) {
            $node = $dwc->createElementNS('http://rs.tdwg.org/dwc/terms/', 'dwc:'.$name);
        }
        if ($node instanceof \DOMElement) {
            $node->appendChild($textNode);
            return $node;
        }
        else {
            throw new \Exception(sprintf('The namespace element %s in extension %s can not be found'), $term, get_class($this->getExtension()));
        }
    }
}