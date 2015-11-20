<?php
namespace AppBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
 
use Doctrine\DBAL\Types\Type;
/**
* Type that maps an Raw id SQL to php objects
* @author Jordan Samouh
*/
class RawidType extends Type
{
    public function getName()
    {
        return 'rawid';
    }

    public function canRequireSQLConversion()
    {
        //return true;
    }
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getDoctrineTypeMapping('RAWID');
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
    //return ($value === null) ? null : base64_encode($value);
        //return ($value === null) ? null : strtoupper(bin2hex($value));
        return $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : strtoupper(bin2hex($value));
    }
}
//See more at: http://symfony2.ylly.fr/add-new-data-type-in-doctrine-2-in-symfony-2-jordscream/#sthash.MvJMLVa4.dpuf
