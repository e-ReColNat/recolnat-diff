<?php

namespace AppBundle\Business\User;

use AppBundle\Entity\Institution;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of User
 *
 * @author tpateffoz
 */
class User
{

    private $institutionCode;
    /* @var $prefs \AppBundle\Business\User\Prefs */
    private $prefs;
    private $exportPath;
    private $maxItemPerPage;

    /** @var  string */
    private $username;
    /** @var  string */
    private $password;


    /**
     * @param string $institutionCode
     * @return $this
     */
    public function init($institutionCode)
    {
        $this->institutionCode = $institutionCode;
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
     * @return bool
     */
    public function hasGrantedAccess()
    {
        /** Todo : Implement cas identification */
        $grantedAccess = true;
        if ($grantedAccess) {
            $this->institutionCode = 'MHNAIX';
        }
        return $grantedAccess;
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
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
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

    /**
     * @return int
     */
    public function getMaxItemPerPage()
    {
        return $this->maxItemPerPage;
    }

    /**
     * @param int $maxItemPerPage
     * @return User
     */
    public function setMaxItemPerPage($maxItemPerPage)
    {
        $this->maxItemPerPage = $maxItemPerPage;
        return $this;
    }


    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }


}
