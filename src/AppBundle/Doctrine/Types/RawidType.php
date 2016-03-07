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
        return false;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getDoctrineTypeMapping('raw');
    }

    public function convertToDatabaseValue($sqlExpr, AbstractPlatform $platform)
    {
        return $sqlExpr;
    }

    public function convertToPHPValue($sqlExpr, AbstractPlatform $platform)
    {
        return unpack("H*", $sqlExpr)[1] ;
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return sprintf('HEXTORAW(%s)', $sqlExpr);
    }
}

