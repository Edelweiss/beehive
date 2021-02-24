<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller{
    public function aboutAction(){
        return $this->render('PapyrillioBeehiveBundle:Default:about.html.twig');
    }
    public function contactAction(){
        return $this->render('PapyrillioBeehiveBundle:Default:contact.html.twig');
    }
    public function helpAction(){
        return $this->render('PapyrillioBeehiveBundle:Default:help.html.twig');
    }
    public function indexAction(){
        return $this->render('PapyrillioBeehiveBundle:Default:index.html.twig');
    }
}
