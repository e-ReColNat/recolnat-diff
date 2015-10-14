<?php

namespace AppBundle\Tests;

use atoum\AtoumBundle\Test\Controller\ControllerTest;
use atoum\AtoumBundle\Test\Asserters;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Test controller with Doctrine mock
 * 
 * @author Florent Dubost <fdubost.externe@m6.fr> 
 */
abstract class AbstractDoctrineMockedTest extends ControllerTest
{
    protected $database = array();

    protected $mockedManager = null;

    protected $clientOptions;

    protected $clientServer;

    /**
     * {@inheritdoc}
     */
    public function createClient(array $options = array(), array $server = array())
    {
        $this->clientOptions = $options;
        $this->clientServer = $server;

        $client = parent::createClient($options, $server);

        if (!is_null($this->mockedManager)) {
            foreach ($this->mockedManager as $serviceName => $managerMock) {
                $client->getContainer()->set($serviceName, $managerMock);
            }
        }

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSendRequestHandler(& $client, & $crawler, $method)
    {
        $generator = $this->getAsserterGenerator();
        $test = $this;

        return function($path, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true) use (& $client, & $crawler, $method, $generator, $test) {
            // New client generation otherwise kernel is reboot without managers mocks 
            $client = $test->createClient($test->clientOptions, $test->clientServer);

            /** @var $client \Symfony\Bundle\FrameworkBundle\Client */
            $crawler = $client->request($method, $path, $parameters, $files, $server, $content, $changeHistory);
            $asserter = new Asserters\Response($generator);

            return $asserter->setWith($client->getResponse());
        };
    }

    /**
     * Initialize mocks for Doctrine
     * 
     * @param array $managersToMock List of managers to be mocked
     * 
     * @return void
     */
    protected function initDatabaseMock($managersToMock)
    {
        if (is_null($this->mockedManager)) {

            $test = $this;

            foreach ($managersToMock as $manager) {

                $entityName = $manager['entityName'];

                // EntityManager mock
                $entityManagerMock = new \mock\Doctrine\ORM\EntityManager();

                // ClassMetadata mock
                $classMetadata = new \mock\Doctrine\ORM\Mapping\ClassMetadata($entityName);

                $entityClassName = $manager['entityClassName'];
                $this->calling($classMetadata)->getName = function() use ($entityClassName) {
                    return $entityClassName;
                };

                // EntityRepository mock
                $entityRepositoryMock = new \mock\Doctrine\ORM\EntityRepository($entityManagerMock, $classMetadata);

                $this->calling($entityRepositoryMock)->find = function($id) use ($test, $entityName) {
                    if (!empty($test->database[$entityName]) && array_key_exists($id, $test->database[$entityName])) {
                        return clone $test->database[$entityName][$id];
                    }

                    return null;
                };

                $this->calling($entityRepositoryMock)->findBy = function($criteria = [], $sort = null, $limit = null, $start = 0) use ($test, $entityName) {
                    $entities = new ArrayCollection($test->database[$entityName]);
                    $crit     = new Criteria();
                    foreach ($criteria as $field => $value) {
                        $crit->andWhere($crit->expr()->eq($field, $value));
                    }
                    if (!is_null($sort)) {
                        $crit->orderBy($sort);
                    }
                    $crit->setFirstResult($start);
                    $crit->setMaxResults($limit);

                    return $entities->matching($crit)->map(function ($item) {
                        return clone $item;
                    });
                };

                // Overload main EntityManager functions
                $this->calling($entityManagerMock)->getRepository = function() use ($entityRepositoryMock) {
                    return $entityRepositoryMock;
                };

                $this->calling($entityManagerMock)->getClassMetadata = function($entity) use ($classMetadata) {
                    return $classMetadata;
                };

                $this->calling($entityManagerMock)->persist = function($entity) use ($test, $entityName) {
                    if (!$entity->getId()) {
                        if (!empty($test->database[$entityName])) {
                            $entity->setId(count($test->database[$entityName]) + 1);
                        } else {
                            $entity->setId(1);
                        }
                    }
                    $test->database[$entityName][$entity->getId()] = $entity;

                    return true;
                };

                $this->calling($entityManagerMock)->remove = function($entity) use ($test, $entityName) {
                    if (!$entity->getId() || empty($test->database[$entityName][$entity->getId()])) {
                        return false;
                    }

                    unset($test->database[$entityName][$entity->getId()]);

                    return true;
                };

                $mockClass = '\mock' . $manager['className'];
                $managerMock = new $mockClass($entityManagerMock, $entityName);

                $this->mockedManager[$manager['serviceName']] =  $managerMock;
            }
        }
    }

    /**
     * Reset mocked database
     * 
     * @return void
     */
    protected function resetDatabase()
    {
        $this->database = array();
    }
}
