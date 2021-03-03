<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BeehiveController{
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
