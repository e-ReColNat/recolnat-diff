<?php
namespace Recolnat\DarwinCoreBundle\Component;

use Symfony\Component\HttpFoundation\File\File;
use Recolnat\DarwinCoreBundle\Exception\BadFileFormat;
use Symfony\Component\Filesystem\Filesystem;
use Recolnat\DarwinCoreBundle\Exception\UnableToExtractException;
use Recolnat\DarwinCoreBundle\Exception\BadXmlFile;
use Recolnat\DarwinCoreBundle\Component\Extension\Extension;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class Extractor
{
    /**
     * 
     * @var DarwinCoreArchive $darwinCoreArchive
     */
    public $darwinCoreArchive;
    /**
     * 
     * @param string $path
     * @param string $filename
     * @return Extractor
     */
    public function init(File $file)
    {
        $this->darwinCoreArchive = new DarwinCoreArchive();
        $this->file = $file;
        $this->extract();
        $this->parseMetaFile();
        return $this;
    }
    
    private function parseMetaFile() 
    {
        $xmlMetaFileFullPath = sprintf($this->getTmpDir().'%s', 'meta.xml') ;
        if (is_file($xmlMetaFileFullPath)) {
            $xmlMetaFile = new \DOMDocument();
            $xmlMetaFile->load($xmlMetaFileFullPath);
            if ($xmlMetaFile === false) {
                throw new BadXmlFile();
            }

            $coreNode = $xmlMetaFile->getElementsByTagName('core') ;
            if (count($coreNode) == 0) {
                throw new \Exception('There is no core node');
            }
            $coreNode = $coreNode->item(0);
            if (!($coreNode->hasAttributes())) {
                throw new \Exception('Core node has no attributes');
            }
            if (!($coreNode->hasChildNodes())) {
                throw new \Exception('Core node has no childs');
            } 
            /*$this->darwinCoreArchive->setCore(new Extension($coreNode, $this->getTmpDir()));
            $extensionNodes = $xmlMetaFile->getElementsByTagName('extension') ;
            
            if (count($extensionNodes) > 0) {
                foreach ($extensionNodes as $extension) {
                    $this->darwinCoreArchive->setExtension(new Extension($extension, $this->getTmpDir()));
                }
                $this->darwinCoreArchive->getCore()->setLinkedExtensions($this->darwinCoreArchive->getExtensions());
            }*/
            $this->darwinCoreArchive->setCore($this->createExtension($coreNode));
            $extensionNodes = $xmlMetaFile->getElementsByTagName('extension') ;
            
            if (count($extensionNodes) > 0) {
                foreach ($extensionNodes as $extension) {
                    $this->darwinCoreArchive->setExtension($this->createExtension($extension));
                }
                //$this->darwinCoreArchive->getCore()->setLinkedExtensions($this->darwinCoreArchive->getExtensions());
            }
        }
    }
    /**
     * 
     * @param \DOMNode $node
     * @return \Recolnat\DarwinCoreBundle\Component\Extension
     */
    private function createExtension(\DOMNode $node)
    {
        $extensionType = self::convertRowType($node->getAttribute('rowType'));
        $extensionClassName = __NAMESPACE__.'\\Extension\\'.ucfirst($extensionType);
        
        $extension = new $extensionClassName() ;
        
        $this->setMetaExtension($extension, $node);
        $this->extractFields($extension, $node);
        $this->parseCsvFile($extension, $node);
        return $extension;
    }
    
    /**
     * 
     * @param Extension $extension
     * @param \DOMNode $node
     */
    private function parseCsvFile(Extension &$extension, \DOMNode $node)
    {
        $rowCount=0;
        $filesNode = $node->getElementsByTagName('files') ;
        if (count($filesNode) == 0) {
            throw new \Exception('There is no files node');
        }
        if ($node->tagName == 'core') {
            $extension->setCore(TRUE);
        }
        foreach ($filesNode as $file) {
            $tmpFilePath=sprintf($this->getTmpDir().'%s', trim($file->nodeValue));
            if (!is_file($tmpFilePath)) {
                throw new DarwinCoreException(sprintf('Can\'t find the file : %s', $tmpFilePath));
            }
        }
        $file = (new \SplFileObject($tmpFilePath));
        $file->setFlags(
            \SplFileObject::READ_CSV | 
            \SplFileObject::READ_AHEAD | 
            \SplFileObject::SKIP_EMPTY | 
            \SplFileObject::DROP_NEW_LINE);
        $extension->setFile($file);
        $file->setCsvControl($extension->getFieldsTerminatedBy(), $extension->getFieldsEnclosedBy());
        while(!$file->eof() && ($row = $file->fgetcsv()) && $row[0] !== null) {
            $rowCount++;
            if ($extension->getIgnoreHeaderLines() && $rowCount == 1) {
                continue;
            }
            if ($extension->isCore()) {
                $extension->data[$row[$extension->getId()]] = $row;
            }
            else {
                $extension->data[$row[$extension->getCoreId()]] = $row;
            }
        }
    }
    
    /**
     * 
     * @param Extension $extension
     * @param \DOMNode $node
     */
    private function setMetaExtension( Extension &$extension, \DOMNode $node) 
    {
        $extension->setEncoding($node->getAttribute('encoding'));
        $extension->setRowType($node->getAttribute('rowType'));
        $extension->setFieldsTerminatedBy($node->getAttribute('fieldsTerminatedBy'));
        $extension->setLinesTerminatedBy($node->getAttribute('linesTerminatedBy'));
        $extension->setFieldsEnclosedBy($node->getAttribute('fieldsEnclosedBy'));
        $extension->setIgnoreHeaderLines($node->getAttribute('ignoreHeaderLines'));
        $extension->setDateFormat($node->getAttribute('dateFormat'));
    }
     /**
     * Parse index fields
     * @param \DOMNode $node
     */
    private function extractFields( Extension &$extension, \DOMNode $node)
    {
        $fields=array();
        if ($node->tagName == 'core') {
            $extension->setId((int) $node->getElementsByTagName('id')->item(0)->getAttribute('index'));
            $fields[$extension->getId()]['shortTerm'] = 'id' ;
            $extension->indexes[$fields[$extension->getId()]['shortTerm']] = $extension->getId();
        }
        else {
            $extension->setCoreId((int) $node->getElementsByTagName('coreid')->item(0)->getAttribute('index'));
            $fields[$extension->getCoreId()]['shortTerm'] = 'coreId' ;
            $extension->indexes[$fields[$extension->getCoreId()]['shortTerm']] = $extension->getCoreId();
        }
        
        
        $fieldList = $node->getElementsByTagName('field');
        if (count($fieldList) > 0) {
            foreach ($fieldList as $field) {
                $key = (int) $field->getAttribute('index');
                $fields[$key] = $extension->setIndexData($field);
                $extension->indexes[$fields[$key]['shortTerm']] = $key;
            }
        }
        $extension->fields = $fields;
    }
    private function setFile()
    {
        try {
            $this->file=new File($this->getFullPath());
            if ($this->file->guessExtension() !== 'zip') {
                throw new BadFileFormat();
            }
        }
        catch (\Symfony\Component\Filesystem\Exception\FileNotFoundException $e) {
            die ($e->getMessage());
        }
    }
    

    private function extract()
    {
        $zipFile = new \ZipArchive();
        if ($zipFile->open($this->getFullPath()) === TRUE) {
            $fileSystem = new Filesystem();
            $this->clearTmpFiles();
            $fileSystem->mkdir($this->getTmpDir());
            if (! $zipFile->extractTo($this->getTmpDir())) {
                throw new UnableToExtractException();
            }
            $zipFile->close();
        } else {
            throw new BadFileFormat();
        }
    }

    private function clearTmpFiles()
    {
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($this->getTmpDir())) {
            $fileSystem->remove($this->getTmpDir());
        }
    }

    /**
     * @return string
     */
    private function getTmpDir()
    {
        return sprintf('/tmp/%s/', substr($this->file->getFilename(), 0, - 4));
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->file->getPath() . DIRECTORY_SEPARATOR . $this->file->getFilename();
    }
    
    
    public static function convertRowType($rowType) 
    {
        $explodedType = explode('/', $rowType);
        $shortType = end($explodedType);
        if (!in_array($shortType, Extension::ALLOWED_EXTENSION)) {
            throw new \Exception(sprintf('The extension "%s" is not (yet) allowed', $this->shortType));
        }
        return $shortType;
    }
    
    public function getDarwinCoreArchive() {
        return $this->darwinCoreArchive;
    }


}