<?php

namespace AppBundle\Command;


use AppBundle\Business\DiffHandler;
use AppBundle\Business\User\User;
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

    public function __construct($server_login_url, $server_ticket, $request_options, $apiRecolnatUser)
    {
        $this->server_login_url = $server_login_url;
        $this->server_ticket = $server_ticket;
        $this->request_options = $request_options;
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!UtilityService::checkDateFormat($input->getArgument('startDate'))) {
            throw new \Exception('La date doit être au format jj/mm/aaaa');
        }

        $this->collectionCode = $input->getArgument('collectionCode');
        $this->startDate = \DateTime::createFromFormat('d/m/Y',$input->getArgument('startDate'));

        $user = $this->simpleCasAuthentification($input->getArgument('username'), $input->getArgument('password'));

        //$output->writeln(print_r($user->getData()));

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

        $diffHandler->saveDiffs($datas);
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
