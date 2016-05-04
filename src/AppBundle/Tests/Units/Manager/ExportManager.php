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
            $this->container->get('diff.manager'),
            $this->container->getParameter('maxitemperpage')[1],
            $this->container->get('diff.computer')
        );

        $user = new User('fakeuser', '...', '', ["ROLE_USER"]);
        $institution = $managerRegistry->getRepository('AppBundle:Institution')->findOneBy(['institutioncode' => 'MHNAIX']);
        $user->setExportPath($this->container->getParameter('export_path'));
        $user->setInstitution($institution);
        $this->exportManager->init($user)->setCollectionCode('AIX');
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
            ->array(\AppBundle\Manager\ExportManager::orderDiffsByTaxon($diff));
    }
}
