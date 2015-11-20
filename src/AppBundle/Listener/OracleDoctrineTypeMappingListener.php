<?php

namespace AppBundle\Listener;


use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\Common\EventSubscriber;

/**
 * Changes Doctrine's default Oracle-specific column type mapping to Doctrine
 * mapping types. This listener modifies doctrine type mapping for
 * OraclePlatform.
 *
 * See:
 * Doctrine Field Mapping: https://doctrine-orm.readthedocs.org/en/latest/reference/basic-mapping.html#doctrine-mapping-types
 * Relevant Bug Report: http://www.doctrine-project.org/jira/browse/DBAL-434
 * Oracle DATE docs: http://docs.oracle.com/cd/B28359_01/server.111/b28318/datatype.htm#i1847
 */
class OracleDoctrineTypeMappingListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(Events::postConnect);
    }

    /**
     * Doctrine defines its primary database abstraction information in what it
     * calls "Platform" classes (e.g. Doctrine\DBAL\Platforms\AbstractPlatform).
     * Each database Doctrine supports implements a Platform file
     * (e.g. OraclePlatform or MySqlPlatform).
     *
     * \Doctrine\DBAL\Platforms\OraclePlatform maps "DATE" fields to Doctrine's
     * own "datetime" type, which returns it as \DateTime. The problem is that
     * internally, Oracle DOES store time data as part of its "DATE" field (even
     * if it's not visible in its default representation DD-MON-RR ==
     * "30-JUL-13"). Thus the Doctrine core devs thought it best to map the
     * database tyep "DATE" to Doctrine's "datetime" type.
     *
     * But if in your case you will never require time data with your DATE
     * fields this will change Oracle's "DATE" fields to be mapped
     * to Doctrine's "date" mapping type. This is the same behavior as almost
     * every other DBAL driver (except SQLServer, which does its own crazy
     * stuff).
     *
     * @param ConnectionEventArgs $args
     * @return void
     */
    public function postConnect(ConnectionEventArgs $args)
    {
        $args
            ->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('date', 'date');
    }
}

