<?php

namespace AppBundle\Business;


use AppBundle\Manager\UtilityService;

class Taxons extends AbstractFile
{
    const FILENAME = '/taxons.json';

    /**
     * @param string $dirPath
     * @param string $userGroup
     */
    public function __construct($dirPath, $userGroup)
    {
        $path = UtilityService::createFile($dirPath.self::FILENAME, $userGroup);
        parent::__construct($path, 'c+');
    }

    public function getTaxon($catalogNumber)
    {
        $taxons = $this->getData();
        if (isset($taxons[$catalogNumber])) {
            return $taxons[$catalogNumber];
        }

        return null;
    }

    public function getTaxons($catalogNumbers)
    {
        $returnTaxons = [];
        if (count($catalogNumbers)) {
            $taxons = $this->getData();

            foreach ($catalogNumbers as $catalogNumber) {
                if (isset($taxons[$catalogNumber])) {
                    $returnTaxons[$catalogNumber] = $taxons[$catalogNumber];
                }
                else {
                    $returnTaxons[$catalogNumber] = null;
                }
            }
        }

        return $returnTaxons;
    }
}
