<?php

namespace AppBundle\Business\User;

use AppBundle\Manager\UtilityService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of User
 *
 * @author tpateffoz
 */
class User implements UserInterface, \Serializable
{

    /* @var $prefs \AppBundle\Business\User\Prefs */
    private $prefs;
    private $exportPath;
    private $username;
    private $password;
    private $salt;
    protected $roles;

    private $data = null;

    const STR_SEARCH_DIFF_PERMISSION = 'EXEC_DIFF';
    const STR_SUPER_ADMIN_ROLE = 'ROLE_SUPER_ADMIN';
    private $super_admin = null;

    /**
     * @var string
     */
    protected $apiRecolnatUser;

    protected $userGroup;

    public function __construct($username, $apiRecolnatUser, $userGroup)
    {
        $this->username = $username;

        $this->apiRecolnatUser = $apiRecolnatUser;
        $this->userGroup = $userGroup;
        $this->setData();
        $this->setRoles();
    }
    /*
    public function __construct($username, $password, $roles, $salt, $apiRecolnatUser, $userGroup)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;

        $this->apiRecolnatUser = $apiRecolnatUser;
        $this->userGroup = $userGroup;
        $this->setData();
        $this->setRoles();
    }
*/
    /**
     * @param string $exportPath
     * @return $this
     */
    public function init($exportPath)
    {
        $this->setExportPath($exportPath);
        UtilityService::createDir($this->getDataDirPath(), $this->userGroup);
        $this->getPrefs();

        return $this;
    }

    /**
     * Grab data through webservice
     */
    private function setData()
    {
        try {
            $client = new Client();
            $response = $client->get($this->apiRecolnatUser.urlencode($this->getUsername()));
            $this->data = \GuzzleHttp\json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            echo \GuzzleHttp\Psr7\str($e->getRequest());
            echo \GuzzleHttp\Psr7\str($e->getResponse());
        }
    }

    /**
     * @return \StdClass|null
     */
    public function getData()
    {
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

    /**
     * @return array
     */
    public function setRoles()
    {
        $data = $this->getData();

        if (count($data->roles)) {
            foreach($data->roles as $role) {
                $this->roles[] = new Role($role->name);
            }
        }
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        if (is_null($this->super_admin)) {
            $this->super_admin = false;
            foreach ($this->getRoles() as $role) {
                if ($role->getRole() == self::STR_SUPER_ADMIN_ROLE) {
                    $this->super_admin = true;
                }
            }
        }

        return $this->super_admin;
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
        UtilityService::createFile($this->getPrefsFileName(), $this->userGroup);
        $handle = fopen($this->getPrefsFileName(), 'w');
        fwrite($handle, $prefs->toJson());
        fclose($handle);
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

    /**
     * Serializes the content of the current User object
     * @return string
     */
    public function serialize()
    {
        return \json_encode(array($this->username, $this->roles));
    }

    /**
     * Unserializes the given string in the current User object
     * @param $serialized
     */
    public function unserialize($serialized)
    {
        list($this->username, $this->roles) = \json_decode($serialized);
    }

}
