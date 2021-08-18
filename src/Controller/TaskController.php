<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Correction;
use App\Form\TaskType;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends BeehiveController{
  protected $entityManager = null;
  protected $repository = null;
  protected $task = null;
  
  public function new(): Response {
    if ($this->request->getMethod() == 'POST') {
      
      $entityManager = $this->getDoctrine()->getManager();

      $repositoryCorrection = $entityManager->getRepository(Correction::class);
      $correction = $repositoryCorrection->findOneBy(['id' => $this->getParameter('correction_id')]);
      
      if($correction){
        $task = new Task();
        $task->setCorrection($correction);
        
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($this->request);

        if($form->isValid()){
          $entityManager->persist($task);
          $entityManager->flush();
          return new Response(json_encode(['success' => true, 'data' => ['id' => $task->getId()]]));
        }

        $errors = [];
        foreach($this->get('validator')->validate($task) as $error){
          $errors[$error->getPropertyPath()] = $error->getMessage();
        }
        return new Response(json_encode(['success' => false, 'error' => $errors]));  
      }
      return new Response(json_encode(['success' => false, 'error' => ['correction_id' => 'object not found']]));
    }
    return new Response(json_encode(['success' => false, 'error' => ['general' => 'no post data found']]));
  }

  public function snippet($id): Response {
    $this->retrieveTask($id);
    return $this->render('task/snippet.html.twig', ['task' => $this->task]);
  }

  public function delete($id): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Task::class);
    $task = $repository->findOneBy(['id' => $id]);
    $entityManager->remove($task);
    $entityManager->flush();
    return new Response(json_encode(['success' => true]));
  }

  public function update($id): Response {
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
    $this->entityManager = $this->getDoctrine()->getManager();
    $this->repository = $this->entityManager->getRepository(Task::class);
    $this->task = $this->repository->findOneBy(['id' => $id]);
    if(!$this->task){
      throw $this->createNotFoundException('Task #' . $id . ' does not exist');
    }
  }
}
