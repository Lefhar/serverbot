<?php
namespace App\Command;

use App\Controller\CronController;
use App\Repository\RestartRepository;
use App\Repository\ServerRepository;
use App\Library\IppowerLibrary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCronCommand extends Command
{
protected static $defaultName = 'app:execute-cron';

private $restartRepository;
private $ippowerLibrary;
private $entityManager;
private $serverRepository;

public function __construct(RestartRepository $restartRepository, IppowerLibrary $ippowerLibrary, EntityManagerInterface $entityManager, ServerRepository $serverRepository)
{
parent::__construct();
$this->restartRepository = $restartRepository;
$this->ippowerLibrary = $ippowerLibrary;
$this->entityManager = $entityManager;
$this->serverRepository = $serverRepository;
}

protected function configure(): void
{
$this->setDescription('Execute the cron job');
}

protected function execute(InputInterface $input, OutputInterface $output): int
{
// Appel de la méthode index du CronController avec les dépendances injectées
$cronController = new CronController($this->restartRepository, $this->ippowerLibrary, $this->entityManager, $this->serverRepository);
$response = $cronController->index();

// Gestion de la réponse
$output->writeln($response);

return Command::SUCCESS;
}
}
