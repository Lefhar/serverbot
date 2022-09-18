<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\ServerType;
use App\library\IppowerLibrary;
use App\library\ssh_access;
use App\Repository\IdentificationRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\EncryptBundle\Event\EncryptEvent;
use SpecShaper\EncryptBundle\Event\EncryptEvents;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

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
    public function new(Request $request, ServerRepository $serverRepository): Response
    {



        $server = new Server();
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        $server->setDate(new \DateTime());
        $server->setUsers($this->getUser());
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
    public function show(Server $server,ssh_access $ssh_access): Response
    {
        dump(new \DateTime());
    $teste =    $ssh_access->ping($server->getIpv4());
//        dump($ippowerLibrary->etat($server->getIppower()));
        return $this->render('server/show.html.twig', [
            'server' => $server,
            'ping'=>$teste
        ]);
    }

    /**
     * @Route("/ping/{id}", name="app_ping_json", methods={"GET"})
     */
    public function pingPc(Server $server,ssh_access $ssh_access): Response
    {
    $teste =    $ssh_access->ping($server->getIpv4());

       return $this->json(['ping'=>$teste]);
    }

    /**
     * @Route("/pingippower/{id}", name="app_ippower_json", methods={"GET"})
     */
    public function pingIppower(Server $server,IppowerLibrary $ippowerLibrary,EntityManagerInterface $entityManager): Response
    {
    $teste =    $ippowerLibrary->etat($server->getIppower());
//    $server->setDate(new \DateTime());
//    $entityManager->flush();

       return $this->json(['ippower'=>$teste]);
    }

    /**
     * @Route("/restartippower/{id}", name="app_restart_ippower_json", methods={"GET"})
     */
    public function restartIpPower(Server $server,IppowerLibrary $ippowerLibrary): Response
    {
    $teste =    $ippowerLibrary->restart($server->getIppower());

       return $this->json(['ippower'=>$teste]);
    }

    /**
     * @Route("/startippower/{id}", name="app_start_ippower_json", methods={"GET"})
     */
    public function startIpPower(Server $server,IppowerLibrary $ippowerLibrary): Response
    {
    $teste =    $ippowerLibrary->startIppower($server->getIppower());

       return $this->json(['ippower'=>$teste]);
    }

    /**
     * @Route("/stopippower/{id}", name="app_stop_ippower_json", methods={"GET"})
     */
    public function stopIpPower(Server $server,IppowerLibrary $ippowerLibrary): Response
    {
    $teste =    $ippowerLibrary->stopIppower($server->getIppower());

       return $this->json(['ippower'=>$teste]);
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
