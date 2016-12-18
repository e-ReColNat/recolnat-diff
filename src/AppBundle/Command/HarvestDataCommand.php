<?php

namespace AppBundle\Command;

use AppBundle\Manager\GenericEntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class HarvestDataCommand extends ContainerAwareCommand
{
    /** @var  GenericEntityManager */
    private $genericEntityManager;

    public function __construct(
        GenericEntityManager $genericEntityManager
    ) {
        $this->genericEntityManager = $genericEntityManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('export:harvest_data')
            ->setDescription('Harvest Records from catalognumbers')
            ->addArgument(
                'institutionCode',
                InputArgument::REQUIRED,
                'institutionCode ?'
            )
            ->addArgument(
                'collectionCode',
                InputArgument::REQUIRED,
                'collectionCode ?'
            )
            ->addArgument(
                'side',
                InputArgument::REQUIRED,
                'erecolnat or institution or both ?'
            )

            ->addArgument(
                'absFilePathname',
                InputArgument::REQUIRED,
                'absolute path to the file ?'
            )
            ->addArgument(
                'catalogNumbers',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'catalog numbers (separate multiple names with a space)?'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$diffComputer = $this->getContainer()->get('diff.computer');
        $collection = $this->getContainer()->get('utility')->getCollection(
            $input->getArgument('institutionCode'),
            $input->getArgument('collectionCode')
        );

        $catalogNumbers = $input->getArgument('catalogNumbers');
        if (count($catalogNumbers)) {
            $datas = $this->genericEntityManager->getEntitiesLinkedToSpecimens(
                $input->getArgument('side'), $collection, $catalogNumbers, true);

            $fs = new Filesystem();
            $fs->dumpFile($input->getArgument('absFilePathname'), \json_encode($datas, JSON_PRETTY_PRINT));
        }
    }
}
