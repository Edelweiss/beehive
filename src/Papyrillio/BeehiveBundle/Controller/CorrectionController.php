<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CorrectionController extends Controller{
  public function listAction($id){
    return $this->render('PapyrillioBeehiveBundle:Default:about.html.twig');
  }
  public function editAction($id){
    return $this->render('PapyrillioBeehiveBundle:Default:contact.html.twig');
  }
  public function updateAction($id){
    return $this->render('PapyrillioBeehiveBundle:Default:help.html.twig');
  }
  public function showAction($id){

    if(!$id){
      return $this->forward('PapyrillioBeehiveBundle:Correction:list');
    }

    $em = $this->getDoctrine()->getEntityManager();
    $repo = $em->getRepository('PapyrillioBeehiveBundle:Correction');

    $correction = $repo->findOneBy(array('id' => $id));
    
    $log = $em->getRepository('StofDoctrineExtensionsBundle:LogEntry');
    #$log = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
    $logs = $log->getLogEntries($correction);

    if(!$correction){
      throw $this->createNotFoundException('The item does not exist');
    }

    return $this->render('PapyrillioBeehiveBundle:Correction:show.html.twig', array('correction' => $correction, 'logs' => $logs));
  }
}
