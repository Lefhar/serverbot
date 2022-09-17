<?php

namespace App\Controller;

use App\Entity\Identification;
use App\Form\IdentificationType;
use App\Repository\IdentificationRepository;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/Identification")
 */
class IdentificationController extends AbstractController
{
    /**
     * @Route("/", name="app_Identification_index", methods={"GET"})
     */
    public function index(IdentificationRepository $IdentificationRepository): Response
    {
        return $this->render('Identification/index.html.twig', [
            'Identifications' => $IdentificationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_Identification_new", methods={"GET", "POST"})
     */
    public function new(Request $request, IdentificationRepository $IdentificationRepository,EncryptorInterface $encryptor): Response
    {
        $Identification = new Identification();
        $form = $this->createForm(IdentificationType::class, $Identification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $Identification->setName($encryptor->encrypt($form->get('name')->getData()));
            $Identification->setPassword($encryptor->encrypt($form->get('password')->getData()));
            $IdentificationRepository->add($Identification, true);
            return $this->redirectToRoute('app_Identification_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Identification/new.html.twig', [
            'Identification' => $Identification,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_Identification_show", methods={"GET"})
     */
    public function show(Identification $Identification): Response
    {
        return $this->render('Identification/show.html.twig', [
            'Identification' => $Identification,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_Identification_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Identification $Identification, IdentificationRepository $IdentificationRepository): Response
    {
        $form = $this->createForm(IdentificationType::class, $Identification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $IdentificationRepository->add($Identification, true);

            return $this->redirectToRoute('app_Identification_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Identification/edit.html.twig', [
            'Identification' => $Identification,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_Identification_delete", methods={"POST"})
     */
    public function delete(Request $request, Identification $Identification, IdentificationRepository $IdentificationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$Identification->getId(), $request->request->get('_token'))) {
            $IdentificationRepository->remove($Identification, true);
        }

        return $this->redirectToRoute('app_Identification_index', [], Response::HTTP_SEE_OTHER);
    }
}
