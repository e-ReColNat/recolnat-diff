<?php
namespace Recolnat\DarwinCoreBundle\OaiPmh;

use Symfony\Component\DependencyInjection\ContainerAware;
use Naoned\OaiPmhServerBundle\DataProvider\DataProviderInterface;
use Recolnat\DarwinCoreBundle\Component\Extractor;

class OaiDwcDataProvider extends ContainerAware implements DataProviderInterface
{
    /**
     * 
     * @var Extractor
     */
    private $extractor;
    public function __construct(Extractor $extractor)
    {
        $this->extractor = $extractor;
        $extractor->init(__DIR__.'/../Resources/files/example.zip');
    }
    /**
     * @return string Repository name
     */
    public function getRepositoryName()
    {
        return 'Darwin Core Oai-Pmh Server';
    }
    
    /**
     * @return string Repository admin email
     */
    public function getAdminEmail()
    {
        return 'me@home.com';
    }
    
    /**
     * @return \DateTime|string     Repository earliest update change on data
     */
    public function getEarliestDatestamp()
    {
        return "2015-01-01";
    }
    
    /**
     * @param  string $identifier [description]
     * @return array
     */
    public function getRecord($identifier)
    {
        return $this->extractor->getCore()->getRecord($identifier);
    }
    
    /**
     * must return an array of arrays with keys «identifier» and «name»
     * @return array List of all sets, with identifier and name
     */
    public function getSets()
    {
        return array(
            array(
                'identifier' => 'seta',
                'name'       => 'THE set number A',
            ),
            array(
                'identifier' => 'setb',
                'name'       => 'THE set identified by B',
            )
        );
    }
    
    /**
     * Search for records
     * @param  String|null    $setTitle Title of wanted set
     * @param  \DateTime|null $from     Date of last change «from»
     * @param  \DataTime|null $until    Date of last change «until»
     * @return array|ArrayObject        List of items
     */
    public function getRecords($setTitle = null, \DateTime $from = null, \DataTime $until = null)
    {
        $recordSet = $this->extractor->getCore()->getData();
        $datas = array();
        foreach($recordSet as $record) {
            $datas[] = $record; 
        }
        return $datas;
    }
    
    /**
     * Tell me, this «record», in which «set is it ?
     * @param  any   $record An item of elements furnished by getRecords method
     * @return array         List of sets, the record belong to
     */
    public function getSetsForRecord($record)
    {
        return $record->getData('rights');
    }
    
    /**
     * Transform the provided record in an array with Dublin Core, «dc_title»  style
     * @param  any   $record An item of elements furnished by getRecords method
     * @return array         Dublin core data
     */
    public static function dublinizeRecord($record)
    {
        //var_dump($record);
        return array(
            'dc_identifier'  => $record['id'],
            'dc_title'       => $record['catalogNumber'],
            'dc_description' => $record['associatedMedia'],
        );
    }
    
    /**
     * Check if sets are supported by data provider
     * @return boolean check
     */
    public function checkSupportSets()
    {
        return true;
    }
    
    /**
     * Get identifier of id
     * @param  any   $record An item of elements furnished by getRecords method
     * @return string        Record Id
     */
    public static function getRecordId($record)
    {
        return $record->getData('id');
    }
    
    /**
     * Get last change date
     * @param  any   $record An item of elements furnished by getRecords method
     * @return \DateTime|string     Record last change
     */
    public static function getRecordUpdated($record)
    {
        return '2015-01-01';
    }
}