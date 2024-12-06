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

#[AllowDynamicProperties] class CronController extends AbstractController
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
        sshRepository $ssh,IdentificationRepository $identificationRepository, EncryptorInterface $encryptorinterface
    ) {
        $this->restartRepository = $restartRepository;
        $this->entityManager = $entityManager;
        $this->serverRepository = $serverRepository;
        $this->sshRepository = $ssh;
        $this->ippowerLibrary = new IppowerLibrary($identificationRepository,$encryptorinterface,$entityManager,$serverRepository);
        $this->ssh_access = $sshAccess;
        $this->encryptor = $encryptor;
    }



    /**
     * @Route("/cron", name="app_cron", methods={"GET"})
     * @throws TransportExceptionInterface
     */
    public function index(): Response
    {
        $arrayserver = array();
        $server = $this->serverRepository->findAll();
        foreach ($server as $row)
        {
            $ssh = $this->sshRepository->findOneBy(['Server' => $row->getId()]);

            if (!$ssh) {
                dump('No SSH entity found for Server ID: ' . $row->getId());
                continue;
            }
            $this->ssh_access->setMachine($ssh->getServer()->getMachine());
            $this->ssh_access->setIdentifiant($this->encryptor->decrypt($ssh->getIdentifiant()));
            $this->ssh_access->setPassword($this->encryptor->decrypt($ssh->getMotdepasse()));
            $this->ssh_access->setIp($ssh->getServer()->getIpv4());
            $this->ssh_access->setPort($ssh->getPort());

          //  $jsonprocess = $this->ssh_access->connexionSSh();
            $ping = $this->ssh_access->ping($row->getIpv4());
            $etat = $this->ippowerLibrary->etat($row->getIppower());
            dump($ping);
            if($etat=="Actif"){
                $arrayserver[$row->getIppower()]=["nom"=>$row->getNom(),"etat"=>$etat,"statut"=>false,"date"=>$row->getDate(),'ping'=>$ping];
                $date = new \DateTime();
                $date->modify('+10 minutes');
                $date->format('Y-m-d H:i:s');
                $row->setDate($date);
                $this->entityManager->flush();
            }else{
                $dateActuel = new \DateTime();
                if($row->getDate()<= $dateActuel)
                {
                    if($row->getEtat()==1){
                        $this->ippowerLibrary->restart($row->getIppower());
                        $arrayserver[$row->getIppower()]=["etat"=>$etat,"statut"=>true,"date"=>$row->getDate(),'ping'=>$ping];

                    }else{

                        $date = new \DateTime();
                        $date->modify('+10 minutes');
                        $date->format('Y-m-d H:i:s');
                        $row->setDate($date);
                        $this->entityManager->flush();
                        $arrayserver[$row->getIppower()]=["etat"=>$etat,"statut"=>true,"date"=>$row->getDate(),'ping'=>$ping,"temps add"];

                    }

                }
            }

        }
        $restart = $this->restartRepository->findBy(['etat'=>2]);
//dump($restart);
        date_default_timezone_set('Europe/Paris');
        $dateActuel = new \DateTime();
        // dump($dateActuel);
        foreach ($this->restartRepository->findBy(['etat'=>2]) as $row)
        {
//dump($row->getDate());

            if($row->getDate()<= $dateActuel){
                // dump('une date');
                if($this->ippowerLibrary->startByCron($row->getIppower()->getIppower())){
                    $this->entityManager->remove($row);
                    $this->entityManager->flush();
                }


            }
        }
        return $this->json($arrayserver);
    }


}