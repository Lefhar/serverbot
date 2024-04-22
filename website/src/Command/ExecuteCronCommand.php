<?php

namespace App\Command;

use App\Controller\CronController;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ExecuteCronCommand',
    description: 'Execute the cron job',
)]
class ExecuteCronCommand extends Command
{
    private CronController $cronController;
    public function __construct(CronController $cronController)
    {
        parent::__construct();
        $this->cronController = $cronController;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $response = $this->cronController->index();

        // Affichage de la réponse
        $output->writeln($response);


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        return Command::SUCCESS;
    }
}
