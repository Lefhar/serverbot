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
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AllowDynamicProperties]
class CronController extends AbstractController
{
    private RestartRepository $restartRepository;
    private EntityManagerInterface $entityManager;
    private ServerRepository $serverRepository;
    private IppowerLibrary $ippowerLibrary;
    private ssh_access $ssh_access;
    private EncryptorInterface $encryptor;
    private HttpClientInterface $httpClient;
    private string $discordWebhookUrl;

    public function __construct(
        RestartRepository $restartRepository,
        EntityManagerInterface $entityManager,
        ServerRepository $serverRepository,
        EncryptorInterface $encryptor,
        ssh_access $sshAccess,
        SshRepository $ssh,
        IdentificationRepository $identificationRepository,
        EncryptorInterface $encryptorinterface,
        HttpClientInterface $httpClient
    ) {
        $this->restartRepository    = $restartRepository;
        $this->entityManager        = $entityManager;
        $this->serverRepository     = $serverRepository;
        $this->sshRepository        = $ssh;
        $this->ippowerLibrary       = new IppowerLibrary($identificationRepository, $encryptorinterface, $entityManager, $serverRepository);
        $this->ssh_access           = $sshAccess;
        $this->encryptor            = $encryptor;
        $this->httpClient           = $httpClient;
        $this->discordWebhookUrl    = $_ENV['DISCORD_WEBHOOK_URL'] ?? "";
    }

    /**
     * @Route("/cron", name="app_cron", methods={"GET"})
     * @throws TransportExceptionInterface
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

            // Récupérer l'heure actuelle
            $now = new \DateTime();

            // Si le serveur est inactif et est censé être actif (etat = 1)
            // ET que la date stockée est antérieure ou égale à l'heure actuelle
            // (c'est-à-dire qu'il est injoignable depuis au moins 10 minutes)
            if ($serverStatus['etat'] === "Inactif" && $server->getEtat() === 1 && $server->getDate() <= $now) {
                $restartResult = $this->handleServerRestart($server);

                // Après redémarrage, mettre à jour la date pour éviter un redémarrage en boucle
                $newDate = new \DateTime();
                $newDate->modify('+10 minutes');
                $server->setDate($newDate);

                if ($restartResult['redemarrage_effectue']) {
                    $this->sendDiscordNotification($server->getNom(), $restartResult['message']);
                }
                $serverStatus = array_merge($serverStatus, $restartResult);
            }

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
        // Vérifie rapidement la disponibilité SSH
        if (!$this->ssh_access->isSshAvailable()) {
            return [
                "nom"     => $server->getNom(),
                "etat"    => "Inactif",
                "statut"  => false,
                "date"    => $server->getDate(),
                "message" => "Connexion SSH indisponible"
            ];
        }

        // Si SSH est disponible, on continue avec le reste du traitement
        $etat = $this->ippowerLibrary->etat($server->getIppower());
        $ping = $this->ssh_access->ping($server->getIpv4());

        // Si le serveur est accessible, on réinitialise le délai d'inaccessibilité à 10 minutes
        $date = new \DateTime();
        $date->modify('+10 minutes');
        $server->setDate($date);

        return [
            "nom"    => $server->getNom(),
            "etat"   => $etat,
            "statut" => ($etat === "Actif"),
            "date"   => $server->getDate(),
            "ping"   => $ping,
            "ssh"    => "actif"
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function handleServerRestart($server): array
    {
        $etat = $this->ippowerLibrary->etat($server->getIppower());
        if ($etat === 'Actif') {
            $this->ippowerLibrary->restart($server->getIppower());

            return [
                "redemarrage_effectue" => true,
                "message"              => "Redémarrage effectué car serveur actif mais injoignable."
            ];
        }

        return [
            "redemarrage_effectue" => false,
            "message"              => "Pas de redémarrage : serveur inactif selon IPPower."
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

    private function sendDiscordNotification(string $serverName, string $message): void
    {
        $payload = [
            'content' => "🔄 **$serverName** : $message"
        ];

        try {
            $this->httpClient->request('POST', $this->discordWebhookUrl, [
                'json' => $payload
            ]);
        } catch (\Exception $e) {
            dump("Failed to send Discord notification: " . $e->getMessage());
        }
    }
}
