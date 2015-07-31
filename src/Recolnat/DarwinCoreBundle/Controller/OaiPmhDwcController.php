<?php
namespace Recolnat\DarwinCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Naoned\OaiPmhServerBundle\Exception\OaiPmhServerException;
use Naoned\OaiPmhServerBundle\Exception\BadVerbException;
use Naoned\OaiPmhServerBundle\Exception\NoRecordsMatchException;
use Naoned\OaiPmhServerBundle\Exception\NoSetHierarchyException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Naoned\OaiPmhServerBundle\Exception\IdDoesNotExistException;
use Naoned\OaiPmhServerBundle\DataProvider\DataProviderInterface;

class OaiPmhDwcController extends Controller
{
    
    private $availableVerbs = array(
        'GetRecord',
        'Identify',
        'ListIdentifiers',
        'ListMetadataFormats',
        'ListRecords',
        'ListSets',
    );
    
    private $queryParams = array();
    
    /**
     * @Route("/", name="oaipmhdwc")
     * @Template()
     */
    public function indexAction()
    {
        $verb = $this->get('request')->get('verb');
        try {
            if (!in_array($verb, $this->availableVerbs)) {
                throw new BadVerbException();
            }
            $methodName = $verb.'Verb';
            return $this->$methodName();
        } catch (\Exception $e) {
            if ($e instanceof OaiPmhServerException) {
                $reflect = new \ReflectionClass($e);
                //Remove «Exception» at end of class namespace
                $code = substr($reflect->getShortName(), 0, -9);
                // lowercase first char
                $code[0] = strtolower(substr($code, 0, 1));
            } elseif ($e instanceof NotFoundHttpException) {
                $code = 'notFoundError';
            } else {
                $code = 'unknownError';
            }
            return $this->error($code, $e->getMessage());
        }
    }

    public function getRecordAction()
    {
        $dataProvider = $this->getDataProvider();
        $oaiPmhRuler = $this->get('naoned.oaipmh.ruler');
        $this->queryParams = $oaiPmhRuler->retrieveAndCheckArguments(
            $this->getAllArguments(),
            array(
                'metadataPrefix',
                'identifier',
            )
            );
        $oaiPmhRuler->checkMetadataPrefix($this->queryParams);
        $record = $this->retrieveRecord($this->queryParams['identifier']);
    
        return $this->render(
            'RecolnatDarwinCoreBundle:OaiPmhDwc:getRecord.xml.twig',
            array(
                'record'         => $record,
                'queryParams'    => $this->queryParams,
                'metadataPrefix' => $this->queryParams['metadataPrefix'],
            )
            );
    }
    
    private function error($code, $message = '')
    {
        if (!$message) {
            $message = 'Unknown error';
        }
        return $this->render(
            'RecolnatDarwinCoreBundle:OaiPmhDwc:error.xml.twig',
            $viewParams = array(
                'code'        => $code,
                'message'     => $message,
                'queryParams' => $this->queryParams,
            )
            );
    }
    
    private function identifyVerb()
    {
        $dataProvider = $this->getDataProvider();
        $oaiPmhRuler = $this->get('naoned.oaipmh.ruler');
        $this->queryParams = $oaiPmhRuler->retrieveAndCheckArguments(
            $this->getAllArguments()
            );
        return $this->render(
            'RecolnatDarwinCoreBundle:OaiPmhDwc:identify.xml.twig',
            array(
                'dataProvider' => $dataProvider,
                'queryParams'  => $this->queryParams,
            )
            );
    }
    
    private function getRecordVerb()
    {
        $dataProvider = $this->getDataProvider();
        $oaiPmhRuler = $this->get('naoned.oaipmh.ruler');
        $this->queryParams = $oaiPmhRuler->retrieveAndCheckArguments(
            $this->getAllArguments(),
            array(
                'metadataPrefix',
                'identifier',
            )
            );
        $oaiPmhRuler->checkMetadataPrefix($this->queryParams);
        $record = $this->retrieveRecord($this->queryParams['identifier']);
    
        return $this->render(
            'RecolnatDarwinCoreBundle:OaiPmhDwc:getRecord.xml.twig',
            array(
                'record'         => $record,
                'queryParams'    => $this->queryParams,
                'metadataPrefix' => $this->queryParams['metadataPrefix'],
            )
            );
    }
    
    private function listRecordsVerb($headersOnly = false)
    {
        $oaiPmhRuler = $this->get('naoned.oaipmh.ruler');
        $this->queryParams = $oaiPmhRuler->retrieveAndCheckArguments(
            $this->getAllArguments(),
            array('metadataPrefix'),
            array('from','until','set'),
            array('resumptionToken')
            );
        if (!array_key_exists('resumptionToken', $this->queryParams)) {
            $oaiPmhRuler->checkMetadataPrefix($this->queryParams);
        }
    
        $dataProvider = $this->getDataProvider();
        $searchParams = $oaiPmhRuler->getSearchParams(
            $this->queryParams,
            $this->get('session')
            );
        if (isset($searchParams['set']) && !$dataProvider->checkSupportSets()) {
            throw new NoSetHierarchyException();
        }
        $records = $dataProvider->getRecords(
            isset($searchParams['set']) ? $searchParams['set'] : null,
            isset($searchParams['from']) ? new \DateTime($searchParams['from']) : null,
            isset($searchParams['until']) ? new \DateTime($searchParams['until']) : null
            );
        if (!(is_array($records) || $records instanceof \ArrayObject)) {
            throw new \Exception('Implementation error: Records must be an array or an arrayObject');
        }
        if (!count($records)) {
            throw new noRecordsMatchException();
        }
        $resumption = $oaiPmhRuler->getResumption(
            $records,
            $searchParams,
            $this->get('session')
            );
        return $this->render(
            'RecolnatDarwinCoreBundle:OaiPmhDwc:listRecords.xml.twig',
            array(
                'headersOnly'    => $headersOnly,
                'resumption'     => $resumption,
                'metadataPrefix' => $searchParams['metadataPrefix'],
                'queryParams'    => $this->queryParams,
                'records'        => $records,
            )
            );
    }
    
    private function listIdentifiersVerb()
    {
        return $this->listRecordsVerb(true);
    }
    
    private function listMetadataFormatsVerb()
    {
        $oaiPmhRuler = $this->get('naoned.oaipmh.ruler');
        $this->queryParams = $oaiPmhRuler->retrieveAndCheckArguments(
            $this->getAllArguments(),
            array(),
            array('identifier')
            );
        // This is just for checking the record exists
        if (array_key_exists('identifier', $this->queryParams)) {
            $record = $this->retrieveRecord($this->queryParams['identifier']);
        }
        return $this->render(
            'RecolnatDarwinCoreBundle:OaiPmhDwc:listMetadataFormats.xml.twig',
            array(
                'availableMetadata' => $oaiPmhRuler->getAvailableMetadata(),
                'queryParams'       => $this->queryParams,
            )
            );
    }
    
    private function listSetsVerb()
    {
        $oaiPmhRuler = $this->get('naoned.oaipmh.ruler');
        $this->queryParams = $oaiPmhRuler->retrieveAndCheckArguments(
            $this->getAllArguments(),
            array(),
            array(),
            array('resumptionToken')
            );
        $dataProvider = $this->getDataProvider();
        if (!$dataProvider->checkSupportSets()) {
            throw new NoSetHierarchyException();
        }
        $sets = $dataProvider->getSets();
        if ($sets !== null && (!(is_array($sets) || ($sets instanceof \ArrayObject)))) {
            throw new Exception('Implementation error: Sets must be an array or an arrayObject');
        }
        $searchParams = $oaiPmhRuler->getSearchParams(
            $this->queryParams,
            $this->get('session')
            );
        $resumption = $oaiPmhRuler->getResumption(
            $sets,
            $searchParams,
            $this->get('session')
            );
        return $this->render(
            'RecolnatDarwinCoreBundle:OaiPmhDwc:listSets.xml.twig',
            array(
                'query'        => $this->queryParams,
                'resumption'   => $resumption,
                'searchParams' => $searchParams,
                'queryParams'  => $this->queryParams,
            )
            );
    }
    
    private function retrieveRecord($id)
    {
        $dataProvider = $this->getDataProvider();
        $record = $dataProvider->getRecord($id);
        if (!$record) {
            throw new idDoesNotExistException();
        }
        return $record;
    }
    
    private function getDataProvider()
    {
        $service = $this->container->getParameter('naoned.oaipmh_server.data_provider_service_name');
        $dataProvider = $this->get($service);
        if (!$dataProvider instanceof DataProviderInterface) {
            throw new \Exception(sprintf("Class of service %s must implement %s", $service, 'DataProviderInterface'));
        }
        return $dataProvider;
    }
    
    private function getAllArguments()
    {
        return array_merge(
            $this->getRequest()->query->all(),
            $this->getRequest()->request->all()
            );
    }
}