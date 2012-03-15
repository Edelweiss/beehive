<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\IndexEntry;

class IndexController extends BeehiveController{
  
  public function newAction(){
    if($this->getRequest()->getMethod() == 'POST'){
      
      $entityManager = $this->getDoctrine()->getEntityManager();

      $repositoryCorrection = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
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

  public function snippetAction($id){
    $this->retrieveIndex($id);
    return $this->render('PapyrillioBeehiveBundle:Index:snippet.html.twig', array('index' => $this->index));
  }

  public function deleteAction($id){
    $this->retrieveIndex($id);
    $this->getDoctrine()->getEntityManager()->remove($this->index);
    $this->getDoctrine()->getEntityManager()->flush();
    return new Response(json_encode(array('success' => true)));
  }

  public function updateAction($id){
    $this->retrieveIndex($id);
    $setter = 'set' . ucfirst($this->getParameter('elementid'));
    $getter = 'get' . ucfirst($this->getParameter('elementid'));
    $this->index->$setter($this->getParameter('newvalue'));
    $this->entityManager->flush();
    return new Response($this->index->$getter());
  }

  protected function retrieveIndex($id){
    $this->entityManager = $this->getDoctrine()->getEntityManager();
    $this->repository = $this->entityManager->getRepository('PapyrillioBeehiveBundle:IndexEntry');

    $this->index = $this->repository->findOneBy(array('id' => $id));
    
    if(!$this->index){
      throw $this->createNotFoundException('Index #' . $id . ' does not exist');
    }
  }
}
