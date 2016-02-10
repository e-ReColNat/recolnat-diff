<?php

namespace AppBundle\Business\User;

/**
 * Description of Prefs
 *
 * @author tpateffoz
 */
class Prefs
{

    protected $dwcDelimiter = ";";
    protected $dwcEnclosure = "";
    protected $dwcLineBreak = "\\n";
    protected $dwcDateFormat = "d-m-Y";
    protected $csvDelimiter = ";";
    protected $csvEnclosure = "";
    protected $csvLineBreak = "\\n";
    protected $csvDateFormat = "d-m-Y";

    const DATE_DISPLAY_FORMAT = [
        '18/01/2016' => 'd/m/Y', 
        '18-01-2016' => 'd-m-Y',
        '18012016' => 'dmY',
        '2016/01/18' => 'Y/m/d',
        '2016-01-18' => 'Y-m-d',
        '20160118' => 'Ymd',
        '2016-01-16T15:19:21' => 'Y-m-d\TH:i:s',
    ] ;
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

    public function getDwcDateFormat()
    {
        return $this->dwcDateFormat;
    }

    public function getCsvDateFormat()
    {
        return $this->csvDateFormat;
    }

    public function getDwcDateFormatForDisplay()
    {
        $dateDisplay = array_flip(self::DATE_DISPLAY_FORMAT);
        return $dateDisplay[$this->dwcDateFormat];
    }

    public function getCsvDateFormatForDisplay()
    {
        $dateDisplay = array_flip(self::DATE_DISPLAY_FORMAT);
        return $dateDisplay[$this->csvDateFormat];
    }

    public function setDwcDateFormat($dwcDateFormat)
    {
        $this->dwcDateFormat = $dwcDateFormat;
    }

    public function setCsvDateFormat($csvDateFormat)
    {
        $this->csvDateFormat = $csvDateFormat;
    }

    /**
     * @param array $prefs
     */
    public function load(array $prefs)
    {
        foreach($prefs as $key=>$value) {
            if (method_exists($this, 'set'.$key)) {
                $this->{'set'.$key}($value);
            }
        }
    }

    public function toArray()
    {
        return [
            "dwcDelimiter" => $this->getDwcDelimiter(),
            "dwcEnclosure" => $this->getDwcEnclosure(),
            "dwcLineBreak" => $this->getDwcLineBreak(),
            "dwcDateFormat" => $this->getDwcDateFormat(),
            "csvDelimiter" => $this->getCsvDelimiter(),
            "csvEnclosure" => $this->getCsvEnclosure(),
            "csvLineBreak" => $this->getCsvLineBreak(),
            "csvDateFormat" => $this->getCsvDateFormat(),
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

}
