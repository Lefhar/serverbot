<?php

namespace App\Controller;

use App\Entity\Ippower;
use App\Form\IppowerType;
use App\Repository\IppowerRepository;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ippower")
 */
class IppowerController extends AbstractController
{
    /**
     * @Route("/", name="app_ippower_index", methods={"GET"})
     */
    public function index(IppowerRepository $ippowerRepository): Response
    {
        return $this->render('ippower/index.html.twig', [
            'ippowers' => $ippowerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_ippower_new", methods={"GET", "POST"})
     */
    public function new(Request $request, IppowerRepository $ippowerRepository,EncryptorInterface $encryptor): Response
    {
        $ippower = new Ippower();
        $form = $this->createForm(IppowerType::class, $ippower);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ippower->setName($encryptor->encrypt($form->get('name')->getData()));
            $ippower->setPassword($encryptor->encrypt($form->get('password')->getData()));
            $ippowerRepository->add($ippower, true);
            return $this->redirectToRoute('app_ippower_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ippower/new.html.twig', [
            'ippower' => $ippower,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_ippower_show", methods={"GET"})
     */
    public function show(Ippower $ippower): Response
    {
        return $this->render('ippower/show.html.twig', [
            'ippower' => $ippower,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_ippower_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Ippower $ippower, IppowerRepository $ippowerRepository): Response
    {
        $form = $this->createForm(IppowerType::class, $ippower);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ippowerRepository->add($ippower, true);

            return $this->redirectToRoute('app_ippower_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ippower/edit.html.twig', [
            'ippower' => $ippower,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_ippower_delete", methods={"POST"})
     */
    public function delete(Request $request, Ippower $ippower, IppowerRepository $ippowerRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ippower->getId(), $request->request->get('_token'))) {
            $ippowerRepository->remove($ippower, true);
        }

        return $this->redirectToRoute('app_ippower_index', [], Response::HTTP_SEE_OTHER);
    }
}
