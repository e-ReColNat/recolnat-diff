<?php

namespace AppBundle\Command;

use AppBundle\Business\Exporter\ExportPrefs;
use AppBundle\Business\User\User;
use epierce\CasRestClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Translator;

class ExportCommand extends ContainerAwareCommand
{
    private $serverLoginUrl;
    private $serverTicket;
    private $requestOptions;
    private $apiRecolnatBaseUri;
    private $collectionCode;
    private $institutionCode;
    private $apiRecolnatUserPath;
    /** @var  User */
    private $user;
    /** @var  Translator */
    private $translator;

    public function __construct(
        $serverLoginUrl,
        $serverTicket,
        $requestOptions,
        $apiRecolnatBaseUri,
        $apiRecolnatUserPath
    ) {
        $this->serverLoginUrl = $serverLoginUrl;
        $this->serverTicket = $serverTicket;
        $this->requestOptions = $requestOptions;
        $this->apiRecolnatBaseUri = $apiRecolnatBaseUri;
        $this->apiRecolnatUserPath = $apiRecolnatUserPath;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('diff:export')
            ->setDescription('Export diff in CSV or DWC format')
            ->addArgument(
                'institutionCode',
                InputArgument::REQUIRED,
                'institution code ?'
            )
            ->addArgument(
                'collectionCode',
                InputArgument::REQUIRED,
                'collection code ?'
            )
            ->addArgument(
                'format',
                InputArgument::REQUIRED,
                'format : csv or dwc'
            )
            ->addArgument(
                'username',
                InputOption::VALUE_REQUIRED,
                'login ?'
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'password ?'
            )
            ->addOption(
                'cookieTGC',
                null,
                InputOption::VALUE_REQUIRED,
                'cookieTGC for cas Authentication'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collectionCode = $input->getArgument('collectionCode');
        $this->institutionCode = $input->getArgument('institutionCode');
        $this->translator = $this->getContainer()->get('translator');
        $this->user = $this->getUser($input);
        $collection = $this->getContainer()->get('utility')
            ->getCollection($this->institutionCode, $this->collectionCode, $this->user);
        /** @var ExportPrefs $exportPrefs */
        $exportPrefs = new ExportPrefs();
        $exportPrefs->setSideForNewRecords('recolnat');
        $exportPrefs->setSideForChoicesNotSet('recolnat');

        if (!($exportPrefs instanceof ExportPrefs)) {
            throw new \Exception('parameters must be an instance of ExportPrefs');
        }
        /* @var $exportManager \AppBundle\Manager\ExportManager */
        $exportManager = $this->getContainer()->get('exportmanager')->init($this->user)->setCollection($collection);
        $file = $exportManager->export($input->getArgument('format'), $exportPrefs);
        $output->writeln($file);
    }

    /**
     * @param InputInterface $input
     * @return User
     */
    private function getUser(InputInterface $input)
    {
        if (!empty($input->getOption('cookieTGC'))) {
            $user = $this->userCasTicketVerification($input);

        } else {
            $user = $this->userCasAuthentication($input->getArgument('username'), $input->getOption('password'));
        }

        if (!$user->isManagerFor($this->collectionCode)) {
            /*$this->log($this->translator->trans($input->getArgument('username').' '.'access.denied.wrongPermission', [],
                'exceptions'));
            throw new AccessDeniedException($this->translator->trans('access.denied.wrongPermission', [],
                'exceptions'));*/
        }
        $user->init($this->getContainer()->getParameter('export_path'));

        return $user;
    }

    /**
     * @param InputInterface $input
     * @return User
     * @throws AccessDeniedException
     */
    private function userCasTicketVerification(InputInterface $input)
    {
        $cookieTGC = $input->getOption('cookieTGC');
        $username = $input->getArgument('username');
        if (!empty($cookieTGC) && !empty($username)) {
            $user = new User($username, $this->getContainer()->getParameter('api_recolnat_base_uri'),
                $this->getContainer()->getParameter('api_recolnat_user_path'), []);
            $verifySsl = true;
            $requestOptions = $this->getContainer()->getParameter('request_options');
            if (isset($requestOptions['verify']) && !$requestOptions['verify']) {
                $verifySsl = false;
            }
            try {
                $user->isGrantedByCheckServiceTicket(
                    $cookieTGC,
                    $this->getContainer()->getParameter('server_login_url'),
                    $this->getContainer()->getParameter('api_recolnat_server_ticket_path'),
                    $this->getContainer()->getParameter('api_recolnat_auth_service_url'),
                    $verifySsl);
            } catch (\Exception $e) {
                /*$this->log($input->getArgument('username').' '.$this->translator->trans('access.denied.wrongPermission',
                        [], 'exceptions'));*/
                throw new AccessDeniedException($this->translator->trans('access.denied.wrongPermission', [],
                    'exceptions'));
            }
        } else {
            /*$this->log($input->getArgument('username').' '.$this->translator->trans('access.denied.tgc_username', [],
                    'exceptions'));*/
            throw new AccessDeniedException($this->translator->trans('access.denied.tgc_username', [], 'exceptions'));
        }

        return $user;
    }

    /**
     * @param string $username
     * @param string $password
     * @return User
     * @throws \Exception
     */
    private function userCasAuthentication($username, $password)
    {
        $client = new CasRestClient();
        $client->setCasServer($this->serverLoginUrl);
        $client->setCasRestContext($this->serverTicket);
        $client->setCredentials($username, $password);
        $client->verifySSL($this->verifySsl());

        if ($client->login()) {
            $response = $client->post($this->getServiceRoute());
            if ($response->getStatusCode() == 200) {
                $user = new User($username, $this->apiRecolnatBaseUri, $this->apiRecolnatUserPath,
                    $this->getContainer()->getParameter('user_group'));

                return $user;
            } else {
                /*$this->log($this->translator->trans($username.' access.denied.wrongPermission', [], 'exceptions'));*/
                throw new AccessDeniedException($this->getContainer()->get('translator')
                    ->trans('access.denied.wrongPassword', [], 'exceptions'));
            }
        } else {
            /*$this->log($this->translator->trans($username.' access.denied.wrongPermission', [], 'exceptions'));*/
            throw new AccessDeniedException($this->getContainer()->get('translator')
                ->trans('access.denied.wrongPassword', [], 'exceptions'));
        }
    }

    /**
     * Retourne une route valide (le composant router de symfony 3.1 buggue en cli !)
     * @return string
     */
    private function getServiceRoute()
    {
        $context = $this->getContainer()->get('router')->getContext();

        $path = $this->getContainer()->get('router')->generate('index');
        if ($path{0} == '/') {
            $path = substr($path, 1);
        }
        $pattern = '%1$s://%2$s/%3$s';
        if (!empty($context->getBaseUrl())) {
            $pattern = '%1$s://%2$s/%4$s/%3$s';
        }

        return sprintf($pattern, $context->getScheme(), $context->getHost(), $path, $context->getBaseUrl());
    }

    private function verifySsl()
    {
        if (isset($this->requestOptions['verify']) && !$this->requestOptions['verify']) {
            return false;
        }

        return true;
    }
}
