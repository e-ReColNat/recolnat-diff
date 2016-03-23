<?php

namespace AppBundle\Security;


use PRayno\CasAuthBundle\Security\CasAuthenticator;
use Symfony\Component\HttpFoundation\Request;

class RecolnatAuthenticator extends CasAuthenticator
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
        parent::__construct($config);
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    /**
     * @param Request $request
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        if ($request->get($this->config['query_ticket_parameter'])) {
            // Validate ticket
            $url = $this->config['server_validation_url'].'?'.
                '='.$request->get($this->config['query_ticket_parameter']).
                '&'.$this->config['query_service_parameter'].'='.$request->getUri();
            $streamContext = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ]);

            $string = file_get_contents($url, false, $streamContext);

            $xml = new \SimpleXMLElement($string, 0, false, $this->config['xml_namespace'], true);

            if (isset($xml->authenticationSuccess)) {
                return (array) $xml->authenticationSuccess;
            }
        }

        return null;
    }
}