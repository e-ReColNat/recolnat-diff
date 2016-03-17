<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 15/03/16
 * Time: 11:35
 */

namespace AppBundle\Manager;


use Hoa\Eventsource\Server;

class RecolnatServer extends Server
{
    /**
     * Send an event. and an empty msg to fix the server buffer
     *
     * @param   string  $data     Data.
     * @param   string  $id       ID (empty string to reset).
     * @return  void
     */
    public function send($data, $id = null)
    {
        parent::send($data, $id) ;
        parent::send($this->getFooMsg());
    }

    /**
     * @return string
     */
    private function getFooMsg() {
        $multiplier = 4;
        $size = 1024 * $multiplier;
        $msg = str_pad('', $size);

        return $msg;
    }
}