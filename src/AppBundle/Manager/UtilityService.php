<?php

namespace AppBundle\Manager;


use AppBundle\Business\User\User;
use AppBundle\Entity\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class UtilityService
{

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(ManagerRegistry $managerRegistry, TranslatorInterface $translator)
    {
        $this->managerRegistry = $managerRegistry;
        $this->translator = $translator;
    }

    /**
     * @param string    $institutionCode
     * @param string    $collectionCode
     * @param User|null $user
     * @return Collection|AccessDeniedException
     */
    public function getCollection($institutionCode, $collectionCode, User $user = null)
    {
        $collection = $this->managerRegistry->getManager('default')
            ->getRepository('AppBundle:Collection')->findOneByCollectionAndInstitution($institutionCode,
                $collectionCode);
        if (!is_null($user) && !is_null($collection)) {
            $this->checkUserRight($user, $collection);
        }

        return $collection;
    }

    /**
     * @param $date
     * @return bool
     */
    public static function isDateWellFormatted($date)
    {
        return (boolean) preg_match('#\d{2}/\d{2}/\d{4}#', $date);
    }

    public static function formatRawId($rawId)
    {
        $formattedRawId = preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', '${1}-${2}-${3}-${4}-${5}', $rawId);
        if ($formattedRawId != $rawId) {
            return strtoupper($formattedRawId);
        } else {
            throw new \Exception('RawId is not well formated');
        }
    }

    public static function getFileGroup($dirPath)
    {
        $fileInfoParent = new \SplFileInfo($dirPath);

        return $fileInfoParent->getGroup();
    }

    public static function createDir($path, $group)
    {
        if (!is_dir($path)) {
            mkdir($path);
            chmod($path, 0775);
            chgrp($path, $group);
        }

        return realpath($path);
    }

    /**
     * @param string      $path
     * @param string|null $group
     * @return string
     */
    public static function createFile($path, $group = null)
    {
        if (!file_exists($path)) {
            touch($path);
            chmod($path, 0775);
            if (!is_null($group)) {
                chgrp($path, $group);
            }
        }

        return realpath($path);
    }

    public function checkUserRight(User $user, Collection $collection)
    {
        if (!$user->isManagerFor($collection->getCollectioncode())) {
            throw new AccessDeniedException($this->translator->trans('access.denied.wrongPermission', [],
                'exceptions'));
        }
    }
}
