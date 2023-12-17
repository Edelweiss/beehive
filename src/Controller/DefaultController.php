<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Compilation;

class DefaultController extends BeehiveController{
    public function archive(): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(Compilation::class);
        $bl = $repository->findBy(['collection' => 'BL'], ['volume' => 'ASC']);
        $konk = $repository->findBy(['collection' => 'BL Konk.'], ['volume' => 'ASC']);
        $boep = $repository->findBy(['collection' => 'BOEP'], ['volume' => 'ASC']);
        return $this->render('default/archive.html.twig', ['bl' => $bl, 'konk' => $konk, 'boep' => $boep]);
    }
    public function about(): Response {
        return $this->render('default/about.html.twig');
    }
    public function contact(): Response {
        return $this->render('default/contact.html.twig');
    }
    public function help(): Response {
        return $this->render('default/help.html.twig');
    }
    public function index(): Response {
        return $this->render('default/index.html.twig');
    }
}
