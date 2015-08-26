<?php
namespace Recolnat\DarwinCoreBundle\Component;

use Symfony\Component\HttpFoundation\File\File;
use Recolnat\DarwinCoreBundle\Exception\BadFileFormat;
use Symfony\Component\Filesystem\Filesystem;
use Recolnat\DarwinCoreBundle\Exception\UnableToExtractException;
use Recolnat\DarwinCoreBundle\Exception\UnableToCreateDirException;
use Recolnat\DarwinCoreBundle\Exception\BadXmlFile;
use Recolnat\DarwinCoreBundle\Exception\DarwinCoreException;
use Recolnat\DarwinCoreBundle\Component\Extension\Extension;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\SplFileInfo;

class Extractor
{

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $path;

    /**
     * @var File
     */
    private $file;

    /**
     * 
     * @var Extension
     */
    protected $core;
    
    /**
     * @var \ArrayObject
     */
    protected $extensions;

    /**
     * 
     * @param string $path
     * @param string $filename
     * @return Extractor
     */
    public function init($path)
    {
        if (is_file($path)) {
            $file = new \SplFileInfo($path);
            $this->filename = $file->getFilename();
            $this->path = $file->getPath();
            $this->setFile();
            $this->extract();
            $this->parseMetaFile();
        }
        else {
            throw new FileNotFoundException();
        }
        return $this;
    }
    
    public function getCore() {
        return $this->core;
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
            $this->core = new Extension($coreNode, $this->getTmpDir());
            $extensionNodes = $xmlMetaFile->getElementsByTagName('extension') ;
            
            $this->extensions = new \ArrayObject(); 
            if (count($extensionNodes) > 0) {
                foreach ($extensionNodes as $extension) {
                    $this->extensions[] = new Extension($extension, $this->getTmpDir());
                }
                $this->getCore()->setLinkedExtensions($this->extensions);
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
        return sprintf('/tmp/%s/', substr($this->filename, 0, - 4));
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->filename;
    }
}