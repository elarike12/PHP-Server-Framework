<?php

/**
 * 
 * copyright @ WereWolf Labs OÜ.
 */

namespace Framework\Cron\Commands;

use Framework\Cron\CronManager;
use Framework\Cli\CommandInterface;
use Framework\Core\ClassManager;

class Cron implements CommandInterface {
    public ClassManager $classManager;
    public CronManager $cronManager;

    public function __construct(ClassManager $classManager, CronManager $cronManager) {
        $this->classManager = $classManager;
        $this->cronManager = $cronManager;
    }

    public function run(array $commandArgs): string {
        $force = false;
        if (($commandArgs[3] ?? false) == true) {
            $force = true;
        }

        switch (strtolower($commandArgs[1] ?? '')) {
            case 'job':
                $jobName = $commandArgs[2] ?? null;
                if (isset($this->cronManager->getCronJobs()[$jobName])) {
                    $this->cronManager->runCronJob($this->cronManager->getCronJobs()[$jobName], $force);
                    return '';
                }

                return 'Invalid or missing job name!';
            case 'listjobs':
                $jobs = [];
                foreach ($this->cronManager->getCronJobs() as $cron) {
                    $jobs[] = $cron->getName() . ' (' . $cron->getSchedule() . ')';
                }

                return 'Cron jobs: ' . implode(', ', $jobs);
        }

        $string = 'Possible arguments:' . PHP_EOL;
        $string .= '    [job] [job name] <force=false> - Run cron jobs by name.' . PHP_EOL;
        $string .= '    [listjobs] - List cron jobs.';
        return $string;
    }

    public function getDescription(?array $commandArgs = null): string {
        return 'Manage cron jobs from command line.';
    }
}
