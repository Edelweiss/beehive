<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Task;

class TaskController extends BeehiveController{
  protected $entityManager = null;
  protected $repository = null;
  protected $task = null;
  
  public function newAction(){
    if($this->getRequest()->getMethod() == 'POST'){
      
      $entityManager = $this->getDoctrine()->getEntityManager();

      $repositoryCorrection = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
      $correction = $repositoryCorrection->findOneBy(array('id' => $this->getParameter('correction_id')));
      
      if($correction){
        $task = new Task();
        $task->setCorrection($correction);

        $form = $this->createFormBuilder($task)
          ->add('category', 'choice', array('label' => 'Kategorie', 'choices' => array('apis' => 'APIS', 'biblio' => 'Biblio', 'bl' => 'BL', 'ddb' => 'DDB', 'hgv' => 'HGV', 'tm' => 'TM')))
          ->add('description', 'textarea', array('label' => 'Beschreibung'))
          ->getForm();

        $form->bindRequest($this->getRequest());

        if($form->isValid()){
          $entityManager->persist($task);
          $entityManager->flush();
          return new Response(json_encode(array('success' => true, 'data' => array('id' => $task->getId()))));
        }

        $errors = array();
        foreach($this->get('validator')->validate($task) as $error){
          $errors[$error->getPropertyPath()] = $error->getMessage();
        }
        return new Response(json_encode(array('success' => false, 'error' => $errors)));  
      }
      return new Response(json_encode(array('success' => false, 'error' => array('correction_id' => 'object not found'))));
    }
    return new Response(json_encode(array('success' => false, 'error' => array('general' => 'no post data found'))));
  }

  public function snippetAction($id){
    $this->retrieveTask($id);
    return $this->render('PapyrillioBeehiveBundle:Task:snippet.html.twig', array('task' => $this->task));
  }

  public function deleteAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Task');
    $task = $repository->findOneBy(array('id' => $id));
    $entityManager->remove($task);
    $entityManager->flush();
    return new Response(json_encode(array('success' => true)));
  }

  public function updateAction($id){
    $this->retrieveTask($id);
    
    $response = '';

    if($this->getParameter('elementid') === 'cleared'){
      if($this->getParameter('newvalue') === '✔'){
        $this->task->markAsCleared();
      } else {
        $this->task->markAsToBeDone();
      }
      $this->entityManager->flush();
      $response = $this->task->isCleared() ? '✔' : '✘';
      
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
