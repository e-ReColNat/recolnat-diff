<?php

namespace AppBundle\Command;


use AppBundle\Business\DiffHandler;
use AppBundle\Business\User\User;
use AppBundle\Entity\Collection;
use AppBundle\Manager\UtilityService;
use epierce\CasRestClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SearchDiffCommand extends ContainerAwareCommand
{
    private $server_login_url;
    private $server_ticket;
    private $request_options;
    private $apiRecolnatUser;
    private $collectionCode;

    /**
     * @var \DateTime
     */
    private $startDate;

    public function __construct($serverLoginUrl, $serverTicket, $requestOptions, $apiRecolnatUser)
    {
        $this->server_login_url = $serverLoginUrl;
        $this->server_ticket = $serverTicket;
        $this->request_options = $requestOptions;
        $this->apiRecolnatUser = $apiRecolnatUser;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('diff:search')
            ->setDescription('Search diffs between buffer and the reference database of e-ReColNat')
            ->addArgument(
                'startDate',
                InputArgument::REQUIRED,
                'Start Date ?'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'username ?'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'password ?'
            )
            ->addArgument(
                'collectionCode',
                InputArgument::REQUIRED,
                'collection code ?'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return string
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collectionCode = $input->getArgument('collectionCode');
        if (UtilityService::isDateWellFormatted($input->getArgument('startDate'))) {
            $this->startDate = \DateTime::createFromFormat('d/m/Y', $input->getArgument('startDate'));
        } else {
            throw new \Exception($this->getContainer()->get('translator')->trans('access.denied.wrongDateFormat'));
        }

        $user = $this->simpleCasAuthentification($input->getArgument('username'), $input->getArgument('password'));

        if (!$user->isManagerFor($this->collectionCode)) {
            throw new AccessDeniedException($this->getContainer()->get('translator')->trans('access.denied.wrongPermission'));
        }
        $collection = $this->getContainer()->get('utility')->getCollection($this->collectionCode);
        $institutionCode = $collection->getInstitution()->getInstitutioncode();

        $diffManager = $this->getContainer()->get('diff.newmanager');
        $diffManager->setCollectionCode($this->collectionCode);
        $diffManager->setStartDate($this->startDate);

        $diffManager->harvestDiffs();
        $diffManager->getResultByClassName('Specimen');

        $diffComputer = $this->getContainer()->get('diff.computer');
        $diffComputer->setCollection($collection);

        foreach ($diffManager::ENTITIES_NAME as $entityName) {
            $catalogNumbers[$entityName] = $diffManager->getResultByClassName($entityName);
            $diffComputer->setCatalogNumbers($catalogNumbers);
            $diffComputer->computeClassname($entityName);
        }
        $datas = $diffComputer->getAllDatas();

        $diffHandler = new DiffHandler($this->getContainer()->getParameter('export_path').'/'.$institutionCode);
        $diffHandler->setCollectionCode($this->collectionCode);

        $this->sendSuccesMail($user, $collection);
        $diffHandler->saveDiffs($datas);

        return 'search OK';
    }

    private function sendSuccesMail(User $user, Collection $collection)
    {
        //var_dump($user->getEmail());
        $message = \Swift_Message::newInstance()
            ->setSubject($user->getEmail())
            ->setFrom('thomas@ird.fr')
            ->setTo($user->getEmail())
            ->setBody('blable', 'text/plain'

            );
        $this->getContainer()->get('mailer')->send($message);
    }

    /**
     * @param string $username
     * @param string $password
     * @return User
     * @throws \Exception
     */
    private function simpleCasAuthentification($username, $password)
    {
        $client = new CasRestClient();
        $client->setCasServer($this->server_login_url);
        $client->setCasRestContext($this->server_ticket);
        $client->setCredentials($username, $password);
        if (isset($this->request_options['verify']) && !$this->request_options['verify']) {
            $client->verifySSL(false);
        }

        if ($client->login()) {
            $response = $client->post('https://localhost/recolnat-diff/search');
            if ($response->getStatusCode() == 200) {
                $user = new User($username, $password, null, [], $this->apiRecolnatUser);

                return $user;
            } else {
                throw new AccessDeniedException($this->getContainer()->get('translator')->trans('access.denied.wrongPassword'));
            }
        } else {
            throw new AccessDeniedException($this->getContainer()->get('translator')->trans('access.denied.wrongPassword'));
        }
    }
}
