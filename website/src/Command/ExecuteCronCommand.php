<?php

namespace App\Command;

use App\Controller\CronController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCronCommand extends Command
{
    protected static $defaultName = 'app:execute-cron';

    private $cronController;

    public function __construct(CronController $cronController)
    {
        $this->cronController = $cronController;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Execute the cron logic from CronController.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Call the method from CronController
        $this->cronController->index();

        // Output success message
        $output->writeln('Cron logic executed successfully.');

        return Command::SUCCESS;
    }
}
