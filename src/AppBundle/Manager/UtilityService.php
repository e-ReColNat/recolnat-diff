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

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public static function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = UtilityService::arrayMergeRecursiveDistinct($merged [$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

    public static function removeFiles(array $filePaths)
    {
        foreach ($filePaths as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
}
