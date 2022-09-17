<?php

namespace App\Controller;

use App\Entity\Restart;
use App\library\IppowerLibrary;
use App\Repository\RestartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    /**
     * @Route("/cron", name="app_cron", methods={"GET"})
     */
    public function index(RestartRepository $restartRepository,IppowerLibrary $ippowerLibrary, EntityManagerInterface $entityManager): Response
    {
        $restart = $restartRepository->findBy(['etat'=>2]);
dump($restart);
        date_default_timezone_set('Europe/Paris');
        $dateActuel = new \DateTime();
        foreach ($restartRepository->findBy(['etat'=>2]) as $row)
        {
dump($row->getDate());
dump($dateActuel);
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
