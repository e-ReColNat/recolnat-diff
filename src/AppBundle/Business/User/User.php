<?php

namespace AppBundle\Business\User;

use AppBundle\Entity\Institution;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of User
 *
 * @author tpateffoz
 */
class User implements UserInterface
{

    /* @var $prefs \AppBundle\Business\User\Prefs */
    private $prefs;
    private $exportPath;
    private $username;
    private $password;
    private $salt;
    private $roles;

    /** @var  Institution */
    private $institution;

    private $data = null;

    const STR_SEARCH_DIFF_PERMISSION = 'SAISIE_COLLECTION';

    /**
     * @var string
     */
    protected $apiRecolnatUser;

    public function __construct($username, $password, $salt, array $roles, $apiRecolnatUser)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->apiRecolnatUser = $apiRecolnatUser;
    }

    /**
     * @param string $exportPath
     * @return $this
     */
    public function init($exportPath)
    {
        $this->setExportPath($exportPath);
        $this->createDir();
        $this->getPrefs();

        return $this;
    }

    /**
     * @return \StdClass|null
     */
    public function getData()
    {
        if (is_null($this->data)) {
            try {
                $client = new Client();
                $response = $client->get($this->apiRecolnatUser.urlencode($this->getUsername()));
                $this->data = \GuzzleHttp\json_decode($response->getBody()->getContents());
            } catch (ClientException $e) {
                echo \GuzzleHttp\Psr7\str($e->getRequest());
                echo \GuzzleHttp\Psr7\str($e->getResponse());
            }
        }

        return $this->data;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        $data = $this->getData();

        return (array) $data->permissionResources;
    }

    public function getManagedCollections()
    {
        $managedCollectionCodes = [];
        $permissions = $this->getPermissions();
        if (count($permissions)) {
            foreach ($permissions as $permission) {
                if ($this->isManagerFor($permission->resource->code)) {
                    $managedCollectionCodes[] = $permission->resource->code;
                }
            }
        }

        return $managedCollectionCodes;
    }

    public function getEmail()
    {
        $data = $this->getData();

        return $data->email;
    }

    /**
     * @param String $collectionCode
     * @return bool
     */
    public function isManagerFor($collectionCode)
    {
        $permissions = $this->getPermissions();
        $boolReturn = false;
        if (count($permissions)) {
            foreach ($permissions as $permission) {
                if ($permission->resource->code == $collectionCode &&
                    $permission->permission->name == self::STR_SEARCH_DIFF_PERMISSION
                ) {
                    $boolReturn = true;
                }
            }
        }

        return $boolReturn;
    }

    /**
     * @return void
     */
    private function createDir()
    {
        $fs = new Filesystem();
        if (!$fs->exists($this->getDataDirPath())) {
            $fs->mkdir($this->getDataDirPath(), 0755);
        }
    }

    /**
     * @return Prefs
     */
    public function getPrefs()
    {
        $this->prefs = new Prefs();
        $fs = new Filesystem();

        if (!$fs->exists($this->getPrefsFileName())) {
            $this->savePrefs($this->prefs);
        }

        $handle = fopen($this->getPrefsFileName(), 'r');
        $this->prefs->load(json_decode(fread($handle, filesize($this->getPrefsFileName())), true));

        return $this->prefs;
    }

    /**
     * @param Prefs $prefs
     */
    public function savePrefs(Prefs $prefs)
    {
        $handle = fopen($this->getPrefsFileName(), 'w');
        fwrite($handle, $prefs->toJson());
        fclose($handle);
        chmod($this->getPrefsFileName(), 0755);
    }

    /**
     * @return string
     */
    public function getPrefsFileName()
    {
        return $this->getDataDirPath().'prefs.json';
    }

    /**
     * @return String
     */
    public function getDataDirPath()
    {
        return realpath($this->exportPath).'/'.$this->getUsername().'/';
    }

    /**
     * @return string
     */
    public function getExportPath()
    {
        return $this->exportPath;
    }

    /**
     * @param string $exportPath
     * @return User
     */
    public function setExportPath($exportPath)
    {
        $this->exportPath = $exportPath;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {

    }

    public function __toString()
    {
        return $this->getUsername();
    }

}
