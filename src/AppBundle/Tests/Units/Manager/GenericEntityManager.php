<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 10/03/16
 * Time: 10:54
 */

namespace AppBundle\Tests\Units\Manager;

require_once dirname(__DIR__).'/../../../../app/AppKernel.php';
use atoum\AtoumBundle\Test\Units;

class GenericEntityManager extends Units\Test
{
    /** @var  \AppBundle\Manager\GenericEntityManager */
    public $genericEntityManager;

    protected $diffManager;
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

    protected $collection;
    protected $collectionCode='AIX';


    public function beforeTestMethod($method)
    {
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        // Store the container and the entity manager in test case properties
        $this->container = $this->kernel->getContainer();
        $managerRegistry = $this->container->get('doctrine');
        $this->collection = $managerRegistry->getRepository('AppBundle:Collection')
            ->findOneBy(['collectioncode' => $this->collectionCode]);

        $this->genericEntityManager = new \AppBundle\Manager\GenericEntityManager($managerRegistry);
    }

    public function testGetEntity()
    {
        $this->object(
            $this->genericEntityManager->getEntity('recolnat', 'localisation', '15248883')
        )->isInstanceOf('\AppBundle\Entity\Localisation');
    }

    public function testGetEntitiesByCatalogNumbers()
    {
        $specimens = $this->genericEntityManager->getEntitiesByCatalogNumbers('recolnat', $this->collection, 'specimen',
            $this->catalogNumbers);
        $this->array(
            $specimens
        )
            ->sizeOf($specimens)->isEqualTo(2);
    }

    public function testGetIdentifier()
    {
        $localisation = $this->genericEntityManager->getEntity('recolnat', 'localisation', 15248883);
        $this->string($this->genericEntityManager->getIdentifierName($localisation))->isEqualTo('locationid');

        $this->exception(
            function($localisation) {
                $this->genericEntityManager->getIdentifierName($localisation);
            }
        )
            ->isInstanceOf('Exception');

        $localisation = 'localisation';
        $this->string($this->genericEntityManager->getIdentifierName($localisation))->isEqualTo('locationid');
    }

    public function testGetIdentifierValue()
    {
        $localisation = $this->genericEntityManager->getEntity('recolnat', 'localisation', 15248883);
        $this->integer($this->genericEntityManager->getIdentifierValue($localisation))->isEqualTo(15248883);

        $this->exception(
            function($localisation) {
                $this->genericEntityManager->getIdentifierValue($localisation);
            }
        )
            ->isInstanceOf('Exception');

    }

    public function testGetEntitiesLinkedToSpecimens()
    {
        $this->if($entities = $this->genericEntityManager->getEntitiesLinkedToSpecimens('recolnat',
            $this->collection,
            $this->catalogNumbers))
            ->array($entities)->sizeOf($entities)->isEqualTo(2);

    }

    public function testFormatArraySpecimen()
    {
        $entities = $this->genericEntityManager->getEntitiesLinkedToSpecimens('recolnat', $this->collection, $this->catalogNumbers);
        foreach ($entities as $entity) {
            $this->if($formatEntities = $this->genericEntityManager->formatArraySpecimenForExport($entity))
                ->array($formatEntities)
                ->sizeOf($formatEntities)
                ->isEqualTo(7);
        }
    }

    public function testGetData()
    {
        $this->string($this->genericEntityManager->getData('recolnat', 'localisation', 'continent', 15248883))
            ->isEqualTo('Europe');

        $this->string($this->genericEntityManager->getData('recolnat', 'localisation', 'continent', 15248883))
            ->isEqualTo('Europe');

        $this->string($this->genericEntityManager
            ->getData('recolnat', 'specimen', 'created', '4C05E1FD273543A580B019586085D4D2')
        )->isEqualTo('2015-08-20 00:00:00');

        $this->exception(
            function() {
                $this->genericEntityManager->getData('recolnat', 'localisation', 'foobar', 15248883);
            }
        )
            ->isInstanceOf('Exception');
    }
}
