<?php

namespace AppBundle\Business;


use AppBundle\Manager\GenericEntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionHandler
{
    /** @var  Session */
    protected $sessionManager;

    /** @var  GenericEntityManager */
    protected $genericEntityManager;

    /** @var  string */
    protected $collectionCode;

    /**
     * SessionHandler constructor.
     * @param Session              $sessionManager
     * @param GenericEntityManager $genericEntityManager
     * @param array                $data
     */
    public function __construct(Session $sessionManager, GenericEntityManager $genericEntityManager, array $data)
    {
        $this->sessionManager = $sessionManager;
        $this->genericEntityManager = $genericEntityManager;

        $this->sessionManager->set('stats', $data['stats']);

        unset($data['stats']);
        $this->sessionManager->set('diffs', $data);

        $this->sessionManager->set('specimensCode', $this->getSpecimensCode());
    }

    /**
     * @param DiffHandler $diffHandler
     * @param string      $collectionCode
     */
    public function init(DiffHandler $diffHandler, $collectionCode)
    {
        $this->collectionCode = $collectionCode;
        $doReload = $this->shouldReload();
        if ($doReload) {
            $this->set('choices', $diffHandler->getChoices()->getContent());
        } else {
            $this->set('file', $this->collectionCode);
        }
    }

    /**
     * @return bool
     */
    public function shouldReload()
    {
        $doReload = false;
        if (!($this->sessionManager->has('file') || $this->sessionManager->get('file') != $this->collectionCode)) {
            $doReload = true;
        }
        if (!($this->sessionManager->has('choices')) || empty($this->sessionManager->get('choices'))) {
            $doReload = true;
        }
        return $doReload;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        $this->sessionManager->set($name, $value);
    }

    /**
     * @param string $name
     * @param null   $default
     */
    public function get($name, $default = null)
    {
        $this->sessionManager->get($name, $default);
    }

    /**
     * @return array
     */
    private function getSpecimensCode()
    {
        $stats = $this->sessionManager->get('diffs');
        if (is_array($stats['datas'])) {
            return array_keys($stats['datas']);
        }
        return [];
    }

    /**
     *
     * @return array
     */
    public function getChoices()
    {
        if ($this->sessionManager->has('choices')) {
            return $this->sessionManager->get('choices');
        }
        return [];
    }

    /**
     *
     * @param string $className
     * @param array  $arrayEntity
     * @return array
     */
    public function getChoicesForEntity($className, $arrayEntity)
    {
        $returnChoices = [];
        if (array_key_exists($this->genericEntityManager->getIdentifierName($className), $arrayEntity)) {
            $relationId = $arrayEntity[$this->genericEntityManager->getIdentifierName($className)];

            foreach ($this->getChoices() as $row) {
                if ($row['className'] == $className && $row['relationId'] == $relationId) {
                    $returnChoices[] = $row;
                }
            }
        }
        return $returnChoices;
    }

    /**
     * @return array
     */
    public function getChoicesForDisplay()
    {
        $choices = $this->getChoices();
        $returnChoices = [];
        if (count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['className']])) {
                    $returnChoices[$choice['className']] = [];
                }
                if (!isset($returnChoices[$choice['className']][$choice['relationId']])) {
                    $returnChoices[$choice['className']][$choice['relationId']] = [];
                }
                $returnChoices[$choice['className']][$choice['relationId']][$choice['fieldName']] = $choice['choice'];
            }
        }
        return $returnChoices;
    }

    /**
     * @param array       $datasWithChoices
     * @param string      $index
     * @param string      $className
     * @param array       $arrayEntity
     * @param null|string $indexSubArray
     */
    public function setChoiceForEntity(&$datasWithChoices, $index, $className, $arrayEntity, $indexSubArray = null)
    {
        $choices = $this->getChoicesForEntity($className, $arrayEntity);
        if (count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!is_null($indexSubArray)) {
                    $datasWithChoices[$index][$className][$indexSubArray][$choice['fieldName']] = $choice['data'];
                } else {
                    $datasWithChoices[$index][$className][$choice['fieldName']] = $choice['data'];
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getChoicesBySpecimenCode()
    {
        $choices = $this->getChoices();
        $returnChoices = array();
        if (count($choices) > 0) {
            foreach ($choices as $choice) {
                if (!isset($returnChoices[$choice['specimenCode']])) {
                    $returnChoices[$choice['specimenCode']] = [];
                }
                unset($choice[$choice['specimenCode']]);
                $returnChoices[$choice['specimenCode']][] = $choice;
            }
        }
        return $returnChoices;
    }
}
