<?php

namespace App\Controller;

use App\Entity\Ssh;
use App\Form\SshType;
use App\Repository\SshRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use SpecShaper\EncryptBundle\Event\EncryptEvent;
use SpecShaper\EncryptBundle\Event\EncryptEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
/**
 * @Route("/admin/ssh")
 */
class SshController extends AbstractController
{



    /**
     * @Route("/", name="app_ssh_index", methods={"GET"})
     */
    public function index(SshRepository $sshRepository): Response
    {
        return $this->render('ssh/index.html.twig', [
            'sshes' => $sshRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_ssh_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SshRepository $sshRepository): Response
    {
        $ssh = new Ssh();
        $form = $this->createForm(SshType::class, $ssh);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sshRepository->add($ssh, true);

            return $this->redirectToRoute('app_ssh_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ssh/new.html.twig', [
            'ssh' => $ssh,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_ssh_show", methods={"GET"})
     */
    public function show(Ssh $ssh): Response
    {
        return $this->render('ssh/show.html.twig', [
            'ssh' => $ssh,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_ssh_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Ssh $ssh, SshRepository $sshRepository): Response
    {
        $form = $this->createForm(SshType::class, $ssh);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sshRepository->add($ssh, true);

            return $this->redirectToRoute('app_ssh_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ssh/edit.html.twig', [
            'ssh' => $ssh,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_ssh_delete", methods={"POST"})
     */
    public function delete(Request $request, Ssh $ssh, SshRepository $sshRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ssh->getId(), $request->request->get('_token'))) {
            $sshRepository->remove($ssh, true);
        }

        return $this->redirectToRoute('app_ssh_index', [], Response::HTTP_SEE_OTHER);
    }
}
