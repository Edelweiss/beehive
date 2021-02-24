<?php

namespace Papyrillio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Papyrillio\UserBundle\Entity\User;
use DateTime;

class ProfileController extends Controller
{
    public function indexAction()
    {
        $entityManager = $this->getDoctrine()->getEntityManager();
        $repository = $entityManager->getRepository('PapyrillioUserBundle:User');
        return $this->render('PapyrillioUserBundle:Profile:index.html.twig');
    }
}