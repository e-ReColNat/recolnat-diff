<?php
namespace Recolnat\DarwinCoreBundle\Exception;

use Symfony\Component\DependencyInjection\Exception\ExceptionInterface;

class BadFileFormat extends DarwinCoreException implements ExceptionInterface
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        if (!$message) {
            $message = 'The file extension is not correct';
        }
        return parent::__construct($message, $code, $previous);
    }
}