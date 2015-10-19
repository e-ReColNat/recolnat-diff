<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
/**
 * Description of DiffStratigraphies
 *
 * @author tpateffoz
 */
class DiffStratigraphies extends DiffAbstract
{
    /**
     * Records set venant de la base Recolnat
     * @var array
     */
    public $recordsRecolnat;
    
    /**
     * Records set venant du fichier de l'institution
     * @var array
     */
    public $recordsInstitution;
    /**
     * Holds the Doctrine entity manager for eRecolnat database interaction
     * @var EntityManager 
     */
    protected $emR;
    /**
     * Holds the Doctrine entity manager for Institution database interaction
     * @var EntityManager 
     */
    protected $emD;

    protected $stats=array();
    protected $excludeFieldsName = [] ;
    
    public function __construct(EntityManager $emR, EntityManager $emD)
    {
        $this->emR = $emR;
        $this->emD = $emD;
    }
    
    public function init($ids)
    {
        $this->recordsRecolnat = $this->emR->getRepository('AppBundle:Stratigraphy')
                ->findBySpecimenCodes($ids) ;
        $this->recordsInstitution = $this->emD->getRepository('AppBundle:Stratigraphy')
                ->findBySpecimenCodes($ids) ;

        $this->compare('Stratigraphy');
        return $this;
    }
    protected function getIdSetter()
    {
        return 'getGeologicalcontextid';
    }

}
