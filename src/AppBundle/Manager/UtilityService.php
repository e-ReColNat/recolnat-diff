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
    public static function isDateWellFormatted($date)
    {
        return (boolean) preg_match('#\d{2}(/)\d{2}(/)\d{4}#', $date);
    }

    public static function formatRawId($rawId)
    {
        $formattedRawId = preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/','${1}-${2}-${3}-${4}-${5}' ,$rawId);
        if ($formattedRawId != $rawId) {
            return strtoupper($formattedRawId);
        } else {
            throw new \Exception('RawId is not well formated');
        }
    }
}
