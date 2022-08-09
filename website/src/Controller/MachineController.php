<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/machine")
 */
class MachineController extends AbstractController
{
    /**
     * @Route("/", name="app_machine_index", methods={"GET"})
     */
    public function index(MachineRepository $machineRepository): Response
    {
        return $this->render('machine/index.html.twig', [
            'machines' => $machineRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_machine_new", methods={"GET", "POST"})
     */
    public function new(Request $request, MachineRepository $machineRepository): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machineRepository->add($machine, true);

            return $this->redirectToRoute('app_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('machine/new.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_machine_show", methods={"GET"})
     */
    public function show(Machine $machine): Response
    {
        return $this->render('machine/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_machine_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Machine $machine, MachineRepository $machineRepository): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machineRepository->add($machine, true);

            return $this->redirectToRoute('app_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('machine/edit.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_machine_delete", methods={"POST"})
     */
    public function delete(Request $request, Machine $machine, MachineRepository $machineRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$machine->getId(), $request->request->get('_token'))) {
            $machineRepository->remove($machine, true);
        }

        return $this->redirectToRoute('app_machine_index', [], Response::HTTP_SEE_OTHER);
    }
}
