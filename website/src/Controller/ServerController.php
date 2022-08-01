<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\ServerType;
use App\Repository\ServerRepository;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\EncryptBundle\Event\EncryptEvent;
use SpecShaper\EncryptBundle\Event\EncryptEvents;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/server")
 */
class ServerController extends AbstractController
{


    /**
     * @Route("/", name="app_server_index", methods={"GET"})
     */
    public function index(ServerRepository $serverRepository): Response
    {
        return $this->render('server/index.html.twig', [
            'servers' => $serverRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_server_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ServerRepository $serverRepository,EncryptorInterface $encryptor): Response
    {

//        $event = new EncryptEvent("3DDOXwqZAEEDPJDK8/LI4wDsftqaNCN2kkyt8+QWr8E=<ENC>");
//
//        $dispatcher->dispatch(EncryptEvents::DECRYPT, $event);
       $encrypted = $encryptor->encrypt('abcd');
        $decrypted = $encryptor->decrypt($encrypted);
//        $decrypted = $event->getValue();
        dump($encrypted);
        dump($decrypted);
        $server = new Server();
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $serverRepository->add($server, true);

            return $this->redirectToRoute('app_server_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('server/new.html.twig', [
            'server' => $server,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_server_show", methods={"GET"})
     */
    public function show(Server $server): Response
    {
        return $this->render('server/show.html.twig', [
            'server' => $server,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_server_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Server $server, ServerRepository $serverRepository): Response
    {
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serverRepository->add($server, true);

            return $this->redirectToRoute('app_server_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('server/edit.html.twig', [
            'server' => $server,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_server_delete", methods={"POST"})
     */
    public function delete(Request $request, Server $server, ServerRepository $serverRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$server->getId(), $request->request->get('_token'))) {
            $serverRepository->remove($server, true);
        }

        return $this->redirectToRoute('app_server_index', [], Response::HTTP_SEE_OTHER);
    }
}
