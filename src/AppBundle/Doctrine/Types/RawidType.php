<?php
namespace AppBundle\Doctrine\Types;

use AppBundle\Entity\Repository\SpecimenRepository;
use Doctrine\DBAL\Platforms\AbstractPlatform;

use Doctrine\DBAL\Types\Type;

/**
 * Type that maps an Raw id SQL to php objects
 * @author Jordan Samouh
 * See more at: http://symfony2.ylly.fr/add-new-data-type-in-doctrine-2-in-symfony-2-jordscream/#sthash.MvJMLVa4.dpuf
 */
class RawidType extends Type
{
    public function getName()
    {
        return 'rawid';
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getDoctrineTypeMapping('raw');
        //return 'RAW';
    }

    public function convertToDatabaseValue($sqlExpr, AbstractPlatform $platform)
    {
        /*if ($sqlExpr !== null) {
            return pack('H*', $sqlExpr);
        }*/
//        return hex2bin($sqlExpr);
        //return $sqlExpr;

        //return sprintf("HEXTORAW('%s')", $sqlExpr);
    }

    public function convertToPHPValue($sqlExpr, AbstractPlatform $platform)
    {
        return ($sqlExpr === null) ? null : strtoupper(bin2hex($sqlExpr));
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        if (strstr($sqlExpr, 'HEXTORAW')) {
            sscanf($sqlExpr, "HEXTORAW('%s')", $sqlExpr);
        }
        //return strstr($sqlExpr, 'HEXTORAW') ? $sqlExpr : sprintf('HEXTORAW(%s)', $sqlExpr);
        return sprintf('HEXTORAW(%s)', $sqlExpr);
    }
}

