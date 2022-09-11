<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use App\Repository\SshRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(MachineRepository $machineRepository,SshRepository $sshRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'machine' => $machineRepository->findAll(),
            'ssh' => $sshRepository->findAll(),
        ]);
    }
}
