<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\IndexEntry;
use App\Entity\Correction;
use App\Form\IndexEntryType;
use DateTime;

class IndexController extends BeehiveController{

  public function new(): Response {
    if ($this->request->getMethod() == 'POST') {
      $entityManager = $this->getDoctrine()->getManager();
      $repositoryCorrection = $entityManager->getRepository(Correction::class);

      $correction = $repositoryCorrection->findOneBy(['id' => $this->getParameter('correction_id')]);
      
      if($correction){
        $index = new IndexEntry();
        $index->setCorrection($correction);

        $form = $this->createForm(IndexEntryType::class, $index);
        $form->handleRequest($this->request);

        if($form->isValid()){
          $entityManager->persist($index);
          $entityManager->flush();
          return new Response(json_encode(['success' => true, 'data' => ['id' => $index->getId()]]));
        }

        $errors = [];
        foreach($this->get('validator')->validate($index) as $error){
          $errors[$error->getPropertyPath()] = $error->getMessage();
        }
        return new Response(json_encode(['success' => false, 'error' => $errors]));  
      }
      return new Response(json_encode(['success' => false, 'error' => ['correction_id' => 'object not found']]));
    }
    return new Response(json_encode(['success' => false, 'error' => ['general' => 'no post data found']]));
  }

  public function snippet($id): Response {
    $this->retrieveIndex($id);
    return $this->render('index/snippet.html.twig', ['index' => $this->index]);
  }

  public function delete($id): Response {
    $this->retrieveIndex($id);
    $this->getDoctrine()->getManager()->remove($this->index);
    $this->getDoctrine()->getManager()->flush();
    return new Response(json_encode(['success' => true]));
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

    $this->index = $this->repository->findOneBy(['id' => $id]);
    
    if(!$this->index){
      throw $this->createNotFoundException('Index #' . $id . ' does not exist');
    }
  }
}
