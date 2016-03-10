<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 10/03/16
 * Time: 09:30
 */

namespace AppBundle\Tests\Units\Manager;


use AppBundle\Business\Exporter\ExportPrefs;
use atoum\AtoumBundle\Test\Units;
use Symfony\Component\Filesystem\Filesystem;

class ExportManager extends Units\Test
{

    /** @var  \AppBundle\Manager\ExportManager */
    protected $exportManager;


    /**
     * @var Symfony\Component\HttpKernel\AppKernel
     */
    protected $kernel = null;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    protected $specimenCodes = ['MHNAIX#AIX#AIX017190', 'MHNAIX#AIX#AIX000097'];
    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    public function beforeTestMethod($method)
    {
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        // Store the container and the entity manager in test case properties
        $this->container = $this->kernel->getContainer();
        $managerRegistry = $this->container->get('doctrine');

        $this->exportManager = new \AppBundle\Manager\ExportManager(
            $managerRegistry,
            realpath(__DIR__.'/../../data'),
            $this->container->get('session'),
            $this->container->get('genericentitymanager'),
            $this->container->get('diff.manager'),
            $this->container->getParameter('maxitemperpage')[1],
            $this->container->get('diff.computer')
        );

        $this->exportManager->init('MHNAIX', 'AIX');
    }

    public function testGetDiffs()
    {
        $this->if($diff = $this->exportManager->getDiffs())
            ->array($diff);
        $this->if($diff = $this->exportManager->getDiffs(null, 'specimen'))
            ->array($diff);
        $this->if($diff = $this->exportManager->getDiffs(null, ['specimen', 'localisation']))
            ->array($diff);
    }

    public function testExport()
    {
        $filesystem = new Filesystem();
        $exportPrefs = new ExportPrefs();
        $exportPrefs->setSideForChoicesNotSet('recolnat');
        $exportPrefs->setSideForNewRecords('recolnat');

        $this->if($dwcFilePath = $this->exportManager->export('dwc', $exportPrefs))
            ->string($dwcFilePath);
        $this->boolean($filesystem->exists($dwcFilePath))->isTrue();
    }

    public function testGetDiffsBySpecimensCode()
    {
        $this->if($diff = $this->exportManager->getDiffsBySpecimensCode($this->specimenCodes))
            ->array($diff);
    }
}

