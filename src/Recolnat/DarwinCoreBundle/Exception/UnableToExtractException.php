<?php
namespace Recolnat\DarwinCoreBundle\Exception;

use Symfony\Component\DependencyInjection\Exception\ExceptionInterface;

class UnableToExtractException extends DarwinCoreException implements ExceptionInterface
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        if (!$message) {
            $message = 'Cannot extract the zip file';
        }
        return parent::__construct($message, $code, $previous);
    }
}
