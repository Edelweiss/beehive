<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends BeehiveController{
  protected $entityManager = null;
  protected $repository = null;
  protected $task = null;

  public function updateAction($id){
    $this->retrieveTask($id);
    
    $response = '';

    if($this->getParameter('elementid') === 'cleared'){
      if($this->getParameter('newvalue') === '✓'){
        $this->task->markAsCleared();
      } else {
        $this->task->markAsToBeDone();
      }
      $this->entityManager->flush();
      $response = $this->task->isCleared() ? '✓' : '✘';
      
    } else {
      $setter = 'set' . ucfirst($this->getParameter('elementid'));
      $getter = 'get' . ucfirst($this->getParameter('elementid'));
      $this->task->$setter($this->getParameter('newvalue'));
      $this->entityManager->flush();
      $response = $this->task->$getter();
    }

    return new Response($response);
  }
  
  protected function retrieveTask($id){
    $this->entityManager = $this->getDoctrine()->getEntityManager();
    $this->repository = $this->entityManager->getRepository('PapyrillioBeehiveBundle:Task');

    $this->task = $this->repository->findOneBy(array('id' => $id));
    
    if(!$this->task){
      throw $this->createNotFoundException('Task #' . $id . ' does not exist');
    }
  }
}
