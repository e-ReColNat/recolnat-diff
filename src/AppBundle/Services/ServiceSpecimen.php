<?php
namespace AppBundle\Services;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request ;
use AppBundle\Manager\DiffManager;
/**
 * Description of ServiceSpecimen
 *
 * @author tpateffoz
 */
class ServiceSpecimen
{
    protected $emR;
    protected $emI;
    protected $sessionManager;
    protected $diffManager;
    protected $maxItemPerPage;
    
    public function __construct(EntityManager $emR, EntityManager $emI, Session $sessionManager, DiffManager $diffManager, $maxItemPerPage)
    {
        $this->emR = $emR;
        $this->emI = $emI;
        $this->sessionManager = $sessionManager;
        $this->diffManager = $diffManager;
        $this->maxItemPerPage = $maxItemPerPage;
    }
    /**
     * @param Request $request
     * @param String $institutionCode
     * @param String $selectedClassName
     * @param Array $specimensWithChoices
     * @param Array $choicesToRemove
     * @return array $array
     */
    public function getSpecimenIdsAndDiffsAndStats(Request $request, $institutionCode, $selectedClassName=null, $specimensWithChoices=[], $choicesToRemove=[])
    {
        $session = $this->sessionManager;

        if ($selectedClassName == "all") {$selectedClassName = null;}
        if (!is_null($request->query->get('reset', null))) {
            $session->clear();
        }
        $diffManager = $this->diffManager;
        $results =$diffManager->init($institutionCode, [$selectedClassName], $specimensWithChoices, $choicesToRemove);
        list ($diffs, $specimensCode, $stats) = [$results['diffs'],$results['specimensCode'],$results['stats']] ;
        $session->set('diffs', $diffs);
        $session->set('specimensCode', $specimensCode);
        $session->set('stats', $stats);
        return [$specimensCode, $diffs, $stats];
    }
    
    public function getMaxItemPerPage(Request $request) {
        $session = $this->sessionManager;
        
        $requestMaxItem=$request->get('maxItemPerPage', null);
        if (!is_null($requestMaxItem)) {
            $session->set('maxItemPerPage', (int) $requestMaxItem);
        }
        elseif (!$session->has('maxItemPerPage')) {
            $session->set('maxItemPerPage', $this->maxItemPerPage);
        }
        return $session->get('maxItemPerPage') ;
    }
}
