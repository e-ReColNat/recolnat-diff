<?php

namespace AppBundle\Business\User;

use AppBundle\Entity\Institution;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of User
 *
 * @author tpateffoz
 */
class User implements UserInterface
{

    private $institutionCode;
    /* @var $prefs \AppBundle\Business\User\Prefs */
    private $prefs;
    private $exportPath;


    private $username;
    private $password;
    private $salt;
    private $roles;

    /** @var  Institution */
    private $institution;

    public function __construct($username, $password, $salt, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    /**
     * @param Institution $institution
     * @param string $exportPath
     * @return $this
     */
    public function init($institution, $exportPath)
    {
        $this->setInstitution($institution);
        $this->setExportPath($exportPath);
        $this->createDir();
        $this->getPrefs();
        return $this;
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
        return realpath($this->exportPath).'/'.$this->institutionCode.'/';
    }

    /**
     * @return mixed
     */
    public function getInstitutionCode()
    {
        return $this->institutionCode;
    }

    /**
     * @param mixed $institutionCode
     * @return User
     */
    public function setInstitutionCode($institutionCode)
    {
        $this->institutionCode = $institutionCode;
        return $this;
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

    /**
     * @return Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param Institution $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
        $this->institutionCode = $institution->getInstitutioncode();
    }


}
