<?php

namespace Papyrillio\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('PapyrillioSecurityBundle:Default:index.html.twig', array('name' => $name));
    }
}
