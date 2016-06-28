<?php

namespace AppBundle\Manager;

use AppBundle\Business\Process;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessManager extends \Jack\Symfony\ProcessManager
{
    /** @var  OutputInterface */
    private $output;
    /** @var \SplFileObject | null */
    private $logFile;

    /**
     * @param process[]            $processes
     * @param int                  $maxParallel
     * @param int                  $poll
     * @param OutputInterface|null $output
     * @param null                 $logFile
     */
    public function runParallel(
        array $processes,
        $maxParallel,
        $poll = 1000,
        OutputInterface $output = null,
        $logFile = null
    ) {
        $this->output = $output;
        $this->logFile = $logFile;
        $this->validateProcesses($processes);

        // do not modify the object pointers in the argument, copy to local working variable
        $processesQueue = $processes;

        // fix maxParallel to be max the number of processes or positive
        $maxParallel = min(abs($maxParallel), count($processesQueue));

        // get the first stack of processes to start at the same time
        /** @var Process[] $currentProcesses */
        $currentProcesses = array_splice($processesQueue, 0, $maxParallel);

        // start the initial stack of processes
        foreach ($currentProcesses as $process) {
            $this->log($process->getTimer());
            $process->start();
            $this->sendOutput($process->getStartOutput('json'));
        }

        do {
            // wait for the given time
            usleep($poll);

            // remove all finished processes from the stack
            foreach ($currentProcesses as $index => $process) {
                if (!$process->isRunning()) {
                    if (!empty($process->getErrorOutput())) {
                        $this->log($process->getErrorOutput());
                        throw new ProcessFailedException($process);
                    }
                    $this->sendOutput($process->getEndOutput('json'));
                    $this->log($process->getTimer());
                    unset($currentProcesses[$index]);

                    // directly add and start new process after the previous finished
                    if (count($processesQueue) > 0) {
                        $nextProcess = array_shift($processesQueue);
                        $nextProcess->start();
                        $this->log($nextProcess->getTimer());
                        $this->sendOutput($nextProcess->startOutput);
                        $currentProcesses[] = $nextProcess;
                    }
                }
            }
            // continue loop while there are processes being executed or waiting for execution
        } while (count($processesQueue) > 0 || count($currentProcesses) > 0);
    }

    private function sendOutput($message)
    {
        if (!is_null($this->output)) {
            $this->output->writeln($message);
        }
        $this->log($message);
    }

    private function log($message)
    {
        $date = new \DateTime();
        if (!is_null($this->logFile)) {
            $this->logFile->fwrite($date->format('H:i:s').' : '.$message.PHP_EOL);
        }
    }
}
