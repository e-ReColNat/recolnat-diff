<?php
namespace Recolnat\DarwinCoreBundle\Exception;

use Symfony\Component\DependencyInjection\Exception\ExceptionInterface;

class UnableToCreateDirException extends DarwinCoreException implements ExceptionInterface
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        if (!$message) {
            $message = 'Cannot create the directory for extraction';
        }
        return parent::__construct($message, $code, $previous);
    }
}
