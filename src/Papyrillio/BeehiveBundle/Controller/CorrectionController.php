<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Edition;
use DateTime;

class CorrectionController extends BeehiveController{
  protected $entityManager = null;
  protected $repository = null;
  protected $correction = null;
  protected $logs = null;

  public function listAction(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    $corrections = array();
    
    
    /*$query = $entityManager->createQuery('
        SELECT c, t, count(t.id) FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t
        GROUP BY c.id'
      );
      $corrections = $query->getResult();*/
      //$this->get('logger')->info('*************************' . print_r($corrections, true));

    if ($this->getRequest()->getMethod() == 'POST') {
      $limit = $this->getParameter('rows');
      $page = $this->getParameter('page');
      $offset = $page * $limit - $limit;
      $offset = $offset < 0 ? 0 : $offset;
      $sort = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');
      
      $query = $entityManager->createQuery('SELECT COUNT(c.id) FROM  PapyrillioBeehiveBundle:Correction c');
      $count = $query->getSingleScalarResult();
      
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;
      
      $this->get('logger')->info('******************************* limit: ' . $limit);
      $this->get('logger')->info('******************************* page: ' . $page);
      $this->get('logger')->info('******************************* offset: ' . $offset);
      $this->get('logger')->info('******************************* sort: ' . $sort);
      $this->get('logger')->info('******************************* sortDirection: ' . $sortDirection);
      $this->get('logger')->info('******************************* totalPages: ' . $totalPages);
      
      $orderBy = 'c.' . $sort . ' ' . $sortDirection;
      if($sort == 'edition'){
        //$orderBy = 'c.bl, c.text ' . $sortDirection;
        $orderBy = 'e.sort, e.title ' . $sortDirection;
      }

      $query = $entityManager->createQuery('
        SELECT c FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t JOIN c.edition e
        GROUP BY c.id
        ORDER BY ' . $orderBy
      )->setFirstResult($offset)->setMaxResults($limit);
      
      $corrections = $query->getResult();

      return $this->render('PapyrillioBeehiveBundle:Correction:list.xml.twig', array('corrections' => $corrections, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page));
    } else {
      
      return $this->render('PapyrillioBeehiveBundle:Correction:list.html.twig', array('corrections' => $corrections));
    }
  }
  
  public function newAction(){
    $correction = new Correction();
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $compilationRepository = $entityManager->getRepository('PapyrillioBeehiveBundle:Compilation');
    $editionRepository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');

    $correction->setCompilation($this->getCompilation());
    $correction->setEdition($this->getEdition());

    $form = $this->createFormBuilder($correction)
      ->add('text', 'text')
      ->add('position', 'text', array('required' => false, 'label' => 'Zeile'))
      ->add('description', 'textarea', array('label' => 'Eintrag'))
      ->add('tm', 'number')
      ->add('hgv', 'text')
      ->add('ddb', 'text')
      ->add('source', 'number', array('label' => 'Quelle'))
      ->getForm();

    if ($this->getRequest()->getMethod() == 'POST') {
        
      $form->bindRequest($this->getRequest());

      if ($form->isValid()) {
        $entityManager->persist($correction);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_correctionshow', array('id' => $correction->getId())));
      }
    }

    return $this->render('PapyrillioBeehiveBundle:Correction:new.html.twig', array('form' => $form->createView(), 'compilations' => $compilationRepository->findAll(), 'editions' => $editionRepository->findAll()));
  }

  protected function getCompilation(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Compilation');

    if($this->getRequest()->getMethod() == 'POST'){
      return $repository->findOneBy(array('id' => $this->getParameter('compilation')));
    }else{
      return $repository->findOneBy(array('volume' => 13));
    }
  }

  protected function getEdition(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');

    if($this->getRequest()->getMethod() == 'POST'){
      return $repository->findOneBy(array('id' => $this->getParameter('edition')));
    }else{
      return $repository->findOneBy(array('sort' => 0));
    }
  }

  public function updateAction($id){
    $this->retrieveCorrection($id);
    
    $setter = 'set' . ucfirst($this->getParameter('elementid'));
    $getter = 'get' . ucfirst($this->getParameter('elementid'));
    
    $this->correction->$setter($this->getParameter('newvalue'));
    $this->entityManager->flush();
    
    return new Response($this->correction->$getter());
  }

  public function deleteAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    $correction = $repository->findOneBy(array('id' => $id));
    foreach($correction->getTasks() as $task){
      $entityManager->remove($task);
    }

    $entityManager->remove($correction);
    $entityManager->flush();
    return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_correctionlist'));
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
