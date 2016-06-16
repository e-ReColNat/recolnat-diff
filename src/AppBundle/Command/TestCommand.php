<?php
/**
 * Created by PhpStorm.
 * User: tpateffoz
 * Date: 01/06/16
 * Time: 14:57
 */

namespace AppBundle\Command;


use AppBundle\Business\User\User;
use AppBundle\Manager\UtilityService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('diff:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var User $user */
        $user = new User('tpateffoz', [], $this->getContainer()->getParameter('api_recolnat_user'),
            $this->getContainer()->getParameter('user_group'));
        $user->init($this->getContainer()->getParameter('export_path'));

        $testDir = $user->getDataDirPath().'test/';
        $testFile = $testDir.'test.txt';

        $userGroup = $this->getContainer()->getParameter('user_group') ;

        UtilityService::createDir($testDir, $userGroup);
        UtilityService::createFile($testFile, $userGroup);

        $output->writeln($testDir);
    }

}
