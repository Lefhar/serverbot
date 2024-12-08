<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Ssh;
use App\Library\IppowerLibrary;
use App\Library\ssh_access;
use App\Repository\IdentificationRepository;
use App\Repository\RestartRepository;
use App\Repository\ServerRepository;
use App\Repository\SshRepository;
use Doctrine\ORM\EntityManagerInterface;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AllowDynamicProperties]
class CronController extends AbstractController
{
    private RestartRepository $restartRepository;
    private EntityManagerInterface $entityManager;
    private ServerRepository $serverRepository;
    private IppowerLibrary $ippowerLibrary;
    private ssh_access $ssh_access;
    private EncryptorInterface $encryptor;

    public function __construct(
        RestartRepository $restartRepository,
        EntityManagerInterface $entityManager,
        ServerRepository $serverRepository,
        EncryptorInterface $encryptor,
        ssh_access $sshAccess,
        SshRepository $ssh,
        IdentificationRepository $identificationRepository,
        EncryptorInterface $encryptorinterface
    ) {
        $this->restartRepository = $restartRepository;
        $this->entityManager = $entityManager;
        $this->serverRepository = $serverRepository;
        $this->sshRepository = $ssh;
        $this->ippowerLibrary = new IppowerLibrary($identificationRepository, $encryptorinterface, $entityManager, $serverRepository);
        $this->ssh_access = $sshAccess;
        $this->encryptor = $encryptor;
    }

    /**
     * @Route("/cron", name="app_cron", methods={"GET"})
     */
    public function index(): Response
    {
        $arrayserver = [];
        $servers = $this->serverRepository->findAll();

        foreach ($servers as $server) {
            $ssh = $this->sshRepository->findOneBy(['Server' => $server->getId()]);

            if (!$ssh) {
                dump('No SSH entity found for Server ID: ' . $server->getId());
                continue;
            }

            $this->initializeSshAccess($ssh);

            $serverStatus = $this->monitorServer($server);
            $arrayserver[$server->getIppower()] = $serverStatus;

            $this->entityManager->flush();
        }

        $this->handlePendingRestarts();

        return $this->json($arrayserver);
    }

    private function initializeSshAccess(Ssh $ssh): void
    {
        $this->ssh_access->setMachine($ssh->getServer()->getMachine());
        $this->ssh_access->setIdentifiant($this->encryptor->decrypt($ssh->getIdentifiant()));
        $this->ssh_access->setPassword($this->encryptor->decrypt($ssh->getMotdepasse()));
        $this->ssh_access->setIp($ssh->getServer()->getIpv4());
        $this->ssh_access->setPort($ssh->getPort());
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function monitorServer($server): array
    {
        $jsonprocess = $this->ssh_access->connexionSSh();

        // Vérifie si une erreur est retournée
        if (isset($jsonprocess['error'])) {
            return [
                "nom" => $server->getNom(),
                "etat" => "Inactif",
                "statut" => false,
                "date" => $server->getDate(),
                'error' => $jsonprocess['error']
            ];
        }

        // Si pas d'erreur, vérifie l'état via IPPower
        $etat = $this->ippowerLibrary->etat($server->getIppower());
        $ping = $this->ssh_access->ping($server->getIpv4());

        $date = new \DateTime();
        $date->modify('+10 minutes');
        $server->setDate($date);

        return [
            "nom" => $server->getNom(),
            "etat" => $etat,
            "statut" => ($etat === "Actif"),
            "date" => $server->getDate(),
            'ping' => $ping,
            "ssh" => "actif"
        ];
    }

    private function handleInactiveServer($server): array
    {
        $dateActuel = new \DateTime();
        $ping = $this->ssh_access->ping($server->getIpv4());

        $etat = $this->ippowerLibrary->etat($server->getIppower());

        if ($server->getDate() <= $dateActuel && $server->getEtat() === 1) {
            if ($etat === 'Actif') {
                $this->ippowerLibrary->restart($server->getIppower());
                return [
                    "etat" => "Inactif",
                    "statut" => true,
                    "date" => $server->getDate(),
                    'ping' => $ping,
                    "message" => "Redémarrage effectué car serveur actif mais injoignable."
                ];
            } else {
                return [
                    "etat" => "Inactif",
                    "statut" => false,
                    "date" => $server->getDate(),
                    'ping' => $ping,
                    "message" => "Pas de redémarrage : serveur inactif selon IPPower."
                ];
            }
        }

        $date = new \DateTime();
        $date->modify('+10 minutes');
        $server->setDate($date);

        return [
            "etat" => "Inactif",
            "statut" => false,
            "date" => $server->getDate(),
            'ping' => $ping,
            "message" => "Aucune action requise."
        ];
    }

    private function handlePendingRestarts(): void
    {
        $dateActuel = new \DateTime();
        foreach ($this->restartRepository->findBy(['etat' => 2]) as $row) {
            if ($row->getDate() <= $dateActuel) {
                if ($this->ippowerLibrary->startByCron($row->getIppower()->getIppower())) {
                    $this->entityManager->remove($row);
                }
            }
        }

        $this->entityManager->flush();
    }
}
