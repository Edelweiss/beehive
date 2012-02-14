<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class CorrectionController extends BeehiveController{
  protected $entityManager = null;
  protected $repository = null;
  protected $correction = null;
  protected $logs = null;

  public function listAction($id){
    return $this->render('PapyrillioBeehiveBundle:Default:about.html.twig');
  }
  public function editAction($id){
    return $this->render('PapyrillioBeehiveBundle:Default:contact.html.twig');
  }
  public function updateAction($id){
    $this->retrieveCorrection($id);
    
    $setter = 'set' . ucfirst($this->getParameter('elementid'));
    $getter = 'get' . ucfirst($this->getParameter('elementid'));
    
    $this->correction->$setter($this->getParameter('newvalue'));
    $this->entityManager->flush();
    
    return new Response($this->correction->$getter());
  }
  public function showAction($id){

    if(!$id){
      return $this->forward('PapyrillioBeehiveBundle:Correction:list');
    }

    $this->retrieveCorrection($id);

    return $this->render('PapyrillioBeehiveBundle:Correction:show.html.twig', array('correction' => $this->correction, 'logs' => $this->logs));
  }
  
  protected function retrieveCorrection($id){
    $this->entityManager = $this->getDoctrine()->getEntityManager();
    $this->repository = $this->entityManager->getRepository('PapyrillioBeehiveBundle:Correction');

    $this->correction = $this->repository->findOneBy(array('id' => $id));
    
    if(!$this->correction){
      throw $this->createNotFoundException('Correction #' . $id . ' does not exist');
    }

    $log = $this->entityManager->getRepository('StofDoctrineExtensionsBundle:LogEntry');
    #$log = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
    $this->logs = $log->getLogEntries($this->correction);
    
    
    foreach ($this->correction->getTasks() as $task) {

        $this->logs = array_merge($this->logs, $log->getLogEntries($task));
      
    }
    
    foreach($this->logs as $logEntry){
      $dataStringified = array();
      foreach($logEntry->getData() as $key => $value){
        $dataStringified[$key] = ($value instanceof DateTime ? $value->format('Y-m-d H:i:s') : $value);
      }
      $logEntry->setData($dataStringified);
    }
    
  }
}
