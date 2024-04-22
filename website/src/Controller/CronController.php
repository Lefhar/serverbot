<?php

namespace App\Controller;

use App\Library\IppowerLibrary;
use App\Repository\IdentificationRepository;
use App\Repository\RestartRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{

    private RestartRepository $restartRepository;
    private EntityManagerInterface $entityManager;
    private ServerRepository $serverRepository;
    private $ippowerLibrary;

    public function __construct(RestartRepository $restartRepository , EntityManagerInterface $entityManager, ServerRepository $serverRepository,IdentificationRepository $identificationRepository, EncryptorInterface $encryptorinterface)
    {
        $this->restartRepository = $restartRepository;
        $this->ippowerLibrary = new IppowerLibrary($identificationRepository,$encryptorinterface,$entityManager,$serverRepository);
        $this->entityManager = $entityManager;
        $this->serverRepository = $serverRepository;
    }




    /**
     * @Route("/cron", name="app_cron", methods={"GET"})
     */
    public function index(): Response
    {


        $server = $this->serverRepository->findAll();
        foreach ($server as $row)
        {
           $etat = $this->ippowerLibrary->etat($row->getIppower());
           if($etat=="Actif"){
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
                   }else{

                       $date = new \DateTime();
                       $date->modify('+10 minutes');
                       $date->format('Y-m-d H:i:s');
                       $row->setDate($date);
                       $this->entityManager->flush();
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
  return $this->json(['restart'=>'encours']);
    }


}
