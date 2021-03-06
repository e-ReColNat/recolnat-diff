<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 10/03/16
 * Time: 09:30
 */

namespace AppBundle\Tests\Units\Manager;


use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\User\User;
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

    protected $catalogNumbers = ['AIX017190', 'AIX000097'];
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
            $this->container->get('session'),
            $this->container->get('genericentitymanager'),
            $this->container->getParameter('maxitemperpage')[1],
            $this->container->get('diff.computer'),
            $this->container->getParameter('user_group')
        );

        $user = new User('tpateffoz', $this->container->getParameter('api_recolnat_base_uri'),
            $this->container->getParameter('api_recolnat_user_path'),
            $this->container->getParameter('user_group'));
        $user->setExportPath($this->container->getParameter('export_path'));
        $collection = $this->container->get('utility')->getCollection('MHNAIX', 'AIX');
        $this->exportManager->init($user)->setCollection($collection);
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
        $this->if($diff = $this->exportManager->getDiffsByCatalogNumbers($this->catalogNumbers))
            ->array($diff);
    }

    public function testOrderDiffsByTaxon()
    {
        $this->if($diff = $this->exportManager->getDiffsByCatalogNumbers($this->catalogNumbers))
            ->array($this->exportManager->orderDiffsByTaxon($diff));
    }
}
