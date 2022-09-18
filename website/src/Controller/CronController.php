<?php

namespace App\Controller;

use App\Entity\Restart;
use App\library\IppowerLibrary;
use App\Repository\RestartRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    /**
     * @Route("/cron", name="app_cron", methods={"GET"})
     */
    public function index(RestartRepository $restartRepository,IppowerLibrary $ippowerLibrary, EntityManagerInterface $entityManager,ServerRepository $serverRepository): Response
    {

        $server = $serverRepository->findAll();
        foreach ($server as $row)
        {
           $etat = $ippowerLibrary->etat($row->getIppower());
           if($etat=="Actif"){
               $date = new \DateTime();
               $date->modify('+5 minutes');
               $date->format('Y-m-d H:i:s');
               $row->setDate($date);
               $entityManager->flush();
           }else{
               if($row->getDate()<= new \DateTime())
               {
                   $ippowerLibrary->restart($row->getIppower());
               }
           }

        }
        $restart = $restartRepository->findBy(['etat'=>2]);
//dump($restart);
        date_default_timezone_set('Europe/Paris');
        $dateActuel = new \DateTime();
       // dump($dateActuel);
        foreach ($restartRepository->findBy(['etat'=>2]) as $row)
        {
dump($row->getDate());

            if($row->getDate()<= $dateActuel){
                dump('une date');
                if($ippowerLibrary->startByCron($row->getIppower()->getIppower())){
                    $entityManager->remove($row);
                    $entityManager->flush();
                }


            }
        }
  return $this->json(['restart'=>'encours']);
    }
}
