<?php

namespace AppBundle\Command;


use AppBundle\Manager\DiffComputer;
use AppBundle\Manager\UtilityService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ComputeDiffCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('diff:compute')
            ->setDescription('Compute diffs between buffer and the reference database of e-ReColNat for a specific entity')
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
                'entityName',
                InputArgument::REQUIRED,
                'entityName ?'
            )
            ->addArgument(
                'savePath',
                InputArgument::REQUIRED,
                'savePath ?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $diffComputer = $this->getContainer()->get(DiffComputer::class);
        $collection = $this->getContainer()->get(UtilityService::class)->getCollection(
            $input->getArgument('institutionCode'),
            $input->getArgument('collectionCode')
        );

        if (!is_null($collection)) {
            $savePath = $input->getArgument('savePath');
            $entityName = $input->getArgument('entityName');

            $fileCatalogNumbers = new \SplFileObject($savePath.'/catalogNumbers_'.$entityName.'.json');
            $catalogNumbers[$entityName] = json_decode(file_get_contents($fileCatalogNumbers->getPathname()), true);

            $diffComputer->setCollection($collection);

            $diffComputer->setCatalogNumbers($catalogNumbers);
            $diffComputer->computeClassname($entityName);

            $datas = $diffComputer->getAllDatas();

            $fs = new Filesystem();
            $fs->dumpFile($savePath.'/'.$entityName.'.json', \json_encode($datas, JSON_PRETTY_PRINT));
            $fs->dumpFile($savePath.'/taxons_'.$entityName.'.json', \json_encode($diffComputer->getTaxons()));
        }
    }

}
