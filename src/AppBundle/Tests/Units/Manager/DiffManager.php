<?php
namespace AppBundle\Tests\Units\Manager;

require_once dirname(__DIR__).'/../../../../app/AppKernel.php';
use atoum\AtoumBundle\Test\Units ;
/**
 * Description of DiffManager
 *
 * @author tpateffoz
 */
class DiffManager extends \atoum
{
    protected $diffManager;
        /**
     * @var Symfony\Component\HttpKernel\AppKernel
     */
    protected $kernel = NULL;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

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
        $this->entityManager = $this->container->get('doctrine')->getManager();

        $this->diffManager = new \AppBundle\Manager\DiffManager($this->entityManager);
    }
    
    public function testGetFromClause()
    {
        $strTaxon = ' FROM %s i INNER JOIN RECOLNAT.DETERMINATIONS ON RECOLNAT.DETERMINATIONS.TAXONID = i.TAXONID INNER JOIN RECOLNAT.SPECIMENS s ON s.OCCURRENCEID = RECOLNAT.DETERMINATIONS.OCCURRENCEID AND s.INSTITUTIONCODE = :institutionCode';
        $strRecolte = ' FROM %s i INNER JOIN RECOLNAT.SPECIMENS s ON s.EVENTID = i.EVENTID AND s.INSTITUTIONCODE = :institutionCode';
        $strSpecimen = ' FROM %s i WHERE INSTITUTIONCODE = :institutionCode' ;
        $strStratigraphy = ' FROM %s i INNER JOIN RECOLNAT.SPECIMENS s ON s.GEOLOGICALCONTEXTID = i.GEOLOGICALCONTEXTID AND s.INSTITUTIONCODE = :institutionCode' ;
        $strLocalisation = ' FROM %s i INNER JOIN RECOLNAT.RECOLTES ON RECOLNAT.RECOLTES.LOCATIONID = i.LOCATIONID INNER JOIN RECOLNAT.SPECIMENS s ON s.EVENTID = RECOLNAT.RECOLTES.EVENTID AND s.INSTITUTIONCODE = :institutionCode' ;
        $strBiblio = ' FROM %s i INNER JOIN RECOLNAT.SPECIMENS s ON s.OCCURRENCEID = i.OCCURRENCEID AND s.INSTITUTIONCODE = :institutionCode' ;
        $strDetermination = ' FROM %s i INNER JOIN RECOLNAT.SPECIMENS s ON s.OCCURRENCEID = i.OCCURRENCEID AND s.INSTITUTIONCODE = :institutionCode' ;
        
        $this->string($this->diffManager->getFromClause('AppBundle:Taxon', 'i'))->isEqualTo($strTaxon) ;
        $this->string($this->diffManager->getFromClause('AppBundle:Recolte', 'i'))->isEqualTo($strRecolte) ;
        $this->string($this->diffManager->getFromClause('AppBundle:Specimen', 'i'))->isEqualTo($strSpecimen) ;
        $this->string($this->diffManager->getFromClause('AppBundle:Stratigraphy', 'i'))->isEqualTo($strStratigraphy) ;
        $this->string($this->diffManager->getFromClause('AppBundle:Localisation', 'i'))->isEqualTo($strLocalisation) ;
        $this->string($this->diffManager->getFromClause('AppBundle:Bibliography', 'i'))->isEqualTo($strBiblio) ;
        $this->string($this->diffManager->getFromClause('AppBundle:Determination', 'i'))->isEqualTo($strDetermination) ;
    }
    
    public function testGetAllDiff()
    {
        $allDiff = $this->diffManager->getAllDiff('MNHN');
        $this->array($allDiff)
                ->hasKey('specimens')
                ->hasKey('bibliographies')
                ->hasKey('determinations')
                ->hasKey('localisations')
                ->hasKey('recoltes')
                ->hasKey('stratigraphies')
                ->hasKey('taxons');
    }
}
