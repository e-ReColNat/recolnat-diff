<?php
namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAware;
use Naoned\OaiPmhServerBundle\DataProvider\DataProviderInterface;

class OaiDataProvider extends ContainerAware implements DataProviderInterface
{
    /**
     * @return string Repository name
     */
    public function getRepositoryName()
    {
        return 'My super Oai-Pmh Server';
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
        return array(
            'title'       => 'Dummy content',
            'description' => 'Some more dummy content',
            'sets'        => array('seta', 'setb'),
            'identifier' => 'seta1',
        );
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
        return array(
            array(
                'identifier'  => '1W1',
                'title'       => 'Dummy content 1',
                'description' => 'Some more dummy content',
                'sets'        => array('seta', 'setb'),
            ),
            array(
                'identifier'  => '1W2',
                'title'       => 'Dummy content 2',
                'description' => 'Some more dummy content',
                'sets'        => array('seta'),
            ),
            array(
                'identifier'  => '1W3',
                'title'       => 'Dummy content 3',
                'description' => 'Some more dummy content',
                'sets'        => array('seta'),
            ),
            array(
                'identifier'  => '1W4',
                'title'       => 'Dummy content 4',
                'description' => 'Some more dummy content',
                'sets'        => array('setc'),
            ),
            array(
                'identifier'  => '1W5',
                'title'       => 'Dummy content 5',
                'description' => 'Some more dummy content',
                'sets'        => array('setd'),
            ),
        );
    }
    
    /**
     * Tell me, this «record», in which «set is it ?
     * @param  any   $record An item of elements furnished by getRecords method
     * @return array         List of sets, the record belong to
     */
    public function getSetsForRecord($record)
    {
        return $record['sets'];
    }
    
    /**
     * Transform the provided record in an array with Dublin Core, «dc_title»  style
     * @param  any   $record An item of elements furnished by getRecords method
     * @return array         Dublin core data
     */
    public static function dublinizeRecord($record)
    {
        return array(
            'dc_identifier'  => $record['identifier'],
            'dc_title'       => $record['title'],
            'dc_description' => $record['description'],
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
        return $record['identifier'];
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