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
    
    public function getCore() {
        return $this->darwinCoreArchive->getCore();
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
            $this->darwinCoreArchive->setCore(new Extension($coreNode, $this->getTmpDir()));
            $extensionNodes = $xmlMetaFile->getElementsByTagName('extension') ;
            
            //$this->extensions = new \ArrayObject(); 
            if (count($extensionNodes) > 0) {
                foreach ($extensionNodes as $extension) {
                    $this->darwinCoreArchive->setExtension(new Extension($extension, $this->getTmpDir()));
                }
                $this->getCore()->setLinkedExtensions($this->darwinCoreArchive->getExtensions());
            }
        }
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
}