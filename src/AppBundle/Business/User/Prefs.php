<?php

namespace AppBundle\Business\User;

/**
 * Description of Prefs
 *
 * @author tpateffoz
 */
class Prefs
{

    protected $dwcDelimiter =";" ;
    protected $dwcEnclosure ="" ;
    protected $dwcLineBreak ="\\n" ;
    protected $csvDelimiter =";" ;
    protected $csvEnclosure ="" ;
    protected $csvLineBreak ="\\n" ;

    public function getDwcDelimiter()
    {
        return $this->dwcDelimiter;
    }

    public function getDwcEnclosure()
    {
        return $this->dwcEnclosure;
    }

    public function getDwcLineBreak()
    {
        return $this->dwcLineBreak;
    }

    public function getCsvDelimiter()
    {
        return $this->csvDelimiter;
    }

    public function getCsvEnclosure()
    {
        return $this->csvEnclosure;
    }

    public function getCsvLineBreak()
    {
        return $this->csvLineBreak;
    }

    public function setDwcDelimiter($dwcDelimiter)
    {
        $this->dwcDelimiter = $dwcDelimiter;
    }

    public function setDwcEnclosure($dwcEnclosure)
    {
        $this->dwcEnclosure = $dwcEnclosure;
    }

    public function setDwcLineBreak($dwcLineBreak)
    {
        $this->dwcLineBreak = $dwcLineBreak;
    }

    public function setCsvDelimiter($csvDelimiter)
    {
        $this->csvDelimiter = $csvDelimiter;
    }

    public function setCsvEnclosure($csvEnclosure)
    {
        $this->csvEnclosure = $csvEnclosure;
    }

    public function setCsvLineBreak($csvLineBreak)
    {
        $this->csvLineBreak = $csvLineBreak;
    }

    public function load(array $prefs) {
        if (isset($prefs['dwcDelimiter'])) {
            $this->setDwcDelimiter($prefs['dwcDelimiter']) ;
        }
        if (isset($prefs['dwcEnclosure'])) {
            $this->setDwcEnclosure($prefs['dwcEnclosure']) ;
        }
        if (isset($prefs['dwcLineBreak'])) {
            $this->setDwcLineBreak($prefs['dwcLineBreak']) ;
        }
        
        if (isset($prefs['csvDelimiter'])) {
            $this->setCsvDelimiter($prefs['csvDelimiter']) ;
        }
        if (isset($prefs['csvEnclosure'])) {
            $this->setCsvEnclosure($prefs['csvEnclosure']) ;
        }
        if (isset($prefs['csvLineBreak'])) {
            $this->setCsvLineBreak($prefs['csvLineBreak']) ;
        }
    }
}
