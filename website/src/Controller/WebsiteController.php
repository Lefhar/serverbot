<?php

namespace App\Controller;

use App\Entity\Website;
use App\Form\WebsiteType;
use App\Repository\WebsiteRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/website")
 */
class WebsiteController extends AbstractController
{
    /**
     * @Route("/", name="app_website_index", methods={"GET"})
     */
    public function index(WebsiteRepository $websiteRepository): Response
    {
        return $this->render('website/index.html.twig', [
            'websites' => $websiteRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_website_new", methods={"GET", "POST"})
     */
    public function new(Request $request, WebsiteRepository $websiteRepository): Response
    {
        $website = new Website();
        $form = $this->createForm(WebsiteType::class, $website);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $website->setDate(new \DateTime());
            $website->setFile(1);
            $website->setRemove(0);
            $website->setUsers($this->getUser());
            $websiteRepository->add($website, true);
           // $filesystem = new Filesystem();
//            $path = getcwd() . '/assets/file';
//
//            // mkdir('/var/www/html/dev.serverbot/serverbot/website/public/assets/file',0755);
//            if (!$filesystem->exists($path)) {
//                $filesystem->mkdir($path, 0775);
//                $filesystem->chown($path, "www-data");
//                $filesystem->chgrp($path, "www-data");
//            }
//            if ($form->get('port')->getData() == "80") {
//                $data = "<VirtualHost *:80>
//        ServerAdmin postmaster@" . $form->get('domaine')->getData() . "
//        ServerName " . $form->get('domaine')->getData() . "
//        ProxyPass / http://" . $form->get('ip')->getData() . ":80/
//        ProxyPassReverse / http://" . $form->get('domaine')->getData() . ":80/
//        ProxyPreserveHost On
// </VirtualHost>";
//            } else {
//                $data = "<VirtualHost *:80>
//        ServerAdmin postmaster@" . $form->get('domaine')->getData() . "
//        ServerName " . $form->get('domaine')->getData() . "
//        ProxyPass / http://" . $form->get('ip')->getData() . ":" . $form->get('port')->getData() . "/
//        ProxyPassReverse / http://" . $form->get('domaine')->getData() . ":80/
//        ProxyPreserveHost On
//        RewriteEngine on
//        RewriteCond %{HTTP:UPGRADE} ^WebSocket$ [NC]
//        RewriteCond %{HTTP:CONNECTION} ^Upgrade$ [NC]
//        RewriteRule .* ws://" . $form->get('ip')->getData() . ":" . $form->get('ip')->getData() . "%{REQUEST_URI} [P]
// </VirtualHost>";
//            }
//            $domaine = preg_replace("`[^A-Za-z0-9]+`", "-", $form->get('domaine')->getData());
//            if (!$filesystem->exists($path . '/' . $domaine . '.conf')) {
//
//                $filesystem->touch($path . '/' . $domaine . '.conf');
//                $filesystem->appendToFile($path . '/' . $domaine . '.conf', $data);
//            }


            return $this->redirectToRoute('app_website_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('website/new.html.twig', [
            'website' => $website,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_website_show", methods={"GET"})
     */
    public function show(Website $website): Response
    {
        return $this->render('website/show.html.twig', [
            'website' => $website,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_website_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Website $website, WebsiteRepository $websiteRepository): Response
    {
        $form = $this->createForm(WebsiteType::class, $website);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $website->setFile(1);
            $website->setRemove(0);
            $websiteRepository->add($website, true);

            return $this->redirectToRoute('app_website_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('website/edit.html.twig', [
            'website' => $website,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_website_delete", methods={"POST"})
     */
    public function delete(Request $request, Website $website, WebsiteRepository $websiteRepository,EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $website->getId(), $request->request->get('_token'))) {
            $website->setRemove(1);
            $websiteRepository->add($website, true);
//            $domaine = $website->getDomaine(); // Remplacez par le nom de domaine correspondant
//            $domaine = str_replace('.','-',$domaine);
//// Chemin vers le script shell
//            $scriptPath = "/home/removesite.sh";
//
//// Chemin vers le fichier de configuration à supprimer
//            $configFile = $domaine . ".conf"; // Assurez-vous que c'est le bon nom de fichier
//
//// Exécution du script shell pour supprimer le fichier de configuration
//            $output = [];
//            $exitCode = null;
//            $result = exec("bash $scriptPath $configFile", $output, $exitCode);
//dump($result);
//            if ($exitCode === 0) {
//                echo "Le fichier de configuration a été supprimé avec succès.";
//              //  $websiteRepository->remove($website, true);
//               // return $this->redirectToRoute('app_website_index', [], Response::HTTP_SEE_OTHER);
//            } else {
//                echo "Une erreur s'est produite lors de la suppression du fichier de configuration.";
//            }

        }


    }
}
