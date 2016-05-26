<?php

namespace AppBundle\Manager;


use Doctrine\Common\Persistence\ManagerRegistry;

class UtilityService
{

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $collectionCode
     * @return \AppBundle\Entity\Collection
     */
    public function getCollection($collectionCode)
    {
        return $this->managerRegistry->getManager('default')
            ->getRepository('AppBundle:Collection')->findOneBy(['collectioncode' => $collectionCode]);
    }

    /**
     * @param $date
     * @return bool
     */
    static public function isDateWellFormatted($date)
    {
        return (boolean) preg_match('#\d{2}(/)\d{2}(/)\d{4}#', $date);
    }
}
