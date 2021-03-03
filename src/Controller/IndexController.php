<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\IndexEntry;
use DateTime;

class IndexController extends BeehiveController{
  
  public function new(): Response {
    if($this->getRequest()->getMethod() == 'POST'){
      $entityManager = $this->getDoctrine()->getManager();
      $repositoryCorrection = $entityManager->getRepository(Correction::class);

      $correction = $repositoryCorrection->findOneBy(array('id' => $this->getParameter('correction_id')));
      
      if($correction){
        $index = new IndexEntry();
        $index->setCorrection($correction);

        $form = $this->getForm($index);

        $form->bindRequest($this->getRequest());

        if($form->isValid()){
          $entityManager->persist($index);
          $entityManager->flush();
          return new Response(json_encode(array('success' => true, 'data' => array('id' => $index->getId()))));
        }

        $errors = array();
        foreach($this->get('validator')->validate($index) as $error){
          $errors[$error->getPropertyPath()] = $error->getMessage();
        }
        return new Response(json_encode(array('success' => false, 'error' => $errors)));  
      }
      return new Response(json_encode(array('success' => false, 'error' => array('correction_id' => 'object not found'))));
    }
    return new Response(json_encode(array('success' => false, 'error' => array('general' => 'no post data found'))));
  }

  public function snippet($id): Response {
    $this->retrieveIndex($id);
    return $this->render('index/snippet.html.twig', ['index' => $this->index]);
  }

  public function delete($id): Response {
    $this->retrieveIndex($id);
    $this->getDoctrine()->getManager()->remove($this->index);
    $this->getDoctrine()->getManager()->flush();
    return new Response(json_encode(array('success' => true)));
  }

  public function update($id): Response {
    $this->retrieveIndex($id);
    $setter = 'set' . ucfirst($this->getParameter('elementid'));
    $getter = 'get' . ucfirst($this->getParameter('elementid'));
    $this->index->$setter($this->getParameter('newvalue'));
    $this->entityManager->flush();
    return new Response($this->index->$getter());
  }

  protected function retrieveIndex($id){
    $this->entityManager = $this->getDoctrine()->getManager();
    $this->repository = $this->entityManager->getRepository(IndexEntry::class);

    $this->index = $this->repository->findOneBy(array('id' => $id));
    
    if(!$this->index){
      throw $this->createNotFoundException('Index #' . $id . ' does not exist');
    }
  }
}
