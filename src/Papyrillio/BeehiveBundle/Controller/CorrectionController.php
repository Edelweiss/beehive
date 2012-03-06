<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Edition;
use Papyrillio\BeehiveBundle\Entity\Task;
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
    
    if ($this->getRequest()->getMethod() == 'POST') {
      
      // PARAMETERS
      
      $limit         = $this->getParameter('rows');
      $page          = $this->getParameter('page');
      $offset        = $page * $limit - $limit;
      $offset        = $offset < 0 ? 0 : $offset;
      $sort          = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');

      // ODER BY
      
      $orderBy = ' ORDER BY c.' . $sort . ' ' . $sortDirection;
      if($sort == 'edition'){
        $orderBy = ' ORDER BY e.sort, e.title ' . $sortDirection;
      }
      if($sort == 'compilation'){
        $orderBy = ' ORDER BY c2.volume ' . $sortDirection;
      }

      // WHERE
      
      $where = '';
      if($this->getParameter('_search') == 'true'){
        $where = '';
        $prefix = ' WHERE ';

        foreach(array('tm', 'hgv', 'ddb', 'source', 'text', 'position', 'description', 'creator', 'created', 'status') as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'c.' . $field . ' LIKE \'%' . $this->getParameter($field) . '%\'';
            $prefix = ' AND ';
          }
        }

        if($this->getParameter('edition')){
          $where .= $prefix . '(e.title LIKE \'%' . $this->getParameter('edition') . '%\' OR e.sort LIKE \'%' . $this->getParameter('edition') . '%\')';
          $prefix = ' AND ';
        }

        if($this->getParameter('compilation')){
          $where .= $prefix . '(c2.title LIKE \'%' . $this->getParameter('compilation') . '%\' OR c2.volume LIKE \'%' . $this->getParameter('compilation') . '%\')';
          $prefix = ' AND ';
        }
      }

      // LIMIT

      //$query = $entityManager->createQuery('SELECT COUNT(c.id) FROM  PapyrillioBeehiveBundle:Correction c');      
      $query = $entityManager->createQuery('
        SELECT count(DISTINCT c.id) FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2
        ' . $where
      );
      $count = $query->getSingleScalarResult();
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;
      
      $this->get('logger')->info('******************************* limit: ' . $limit);
      $this->get('logger')->info('******************************* page: ' . $page);
      $this->get('logger')->info('******************************* offset: ' . $offset);
      $this->get('logger')->info('******************************* sort: ' . $sort);
      $this->get('logger')->info('******************************* sortDirection: ' . $sortDirection);
      $this->get('logger')->info('******************************* totalPages: ' . $totalPages);

      // QUERY
      
      $query = $entityManager->createQuery('
        SELECT c FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 ' . $where . ' GROUP BY c.id ' . $orderBy
      )->setFirstResult($offset)->setMaxResults($limit);
      
      $corrections = $query->getResult();

      return $this->render('PapyrillioBeehiveBundle:Correction:list.xml.twig', array('corrections' => $corrections, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page));
    } else {
      return $this->render('PapyrillioBeehiveBundle:Correction:list.html.twig', array('corrections' => $corrections));
    }
  }
  
  public function newAction(){
    $correction = new Correction();
    
    $correction->setCreator($this->get('security.context')->getToken()->getUser()->getUsername());
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $compilationRepository = $entityManager->getRepository('PapyrillioBeehiveBundle:Compilation');
    $editionRepository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');

    $correction->setCompilation($this->getCompilation());
    $correction->setEdition($this->getEdition());

    $form = $this->createFormBuilder($correction)
      ->add('text', 'text')
      ->add('position', 'text', array('required' => false, 'label' => 'Zeile'))
      ->add('description', 'textarea', array('label' => 'Eintrag'))
      ->add('tm', 'number', array('required' => $correction->getEdition()->getSort() == 0 ? false : true))
      ->add('hgv', 'text', array('required' => $correction->getEdition()->getSort() == 0 ? false : true))
      ->add('ddb', 'text', array('required' => $correction->getEdition()->getSort() == 0 ? false : true))
      ->add('source', 'number', array('required' => false, 'label' => 'Quelle'))
      ->getForm();

    if ($this->getRequest()->getMethod() == 'POST') {
        
      $form->bindRequest($this->getRequest());

      if ($form->isValid()) {
        foreach($this->getParameter('task') as $category => $description){
          if(strlen(trim($description))){
            $task = new Task();
            $task->setCategory($category);
            $task->setDescription(trim($description));
            $task->setCorrection($correction);
            $entityManager->persist($task);
          }
        }
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

    $task = new Task();
    $task->setCorrection($this->correction);
    $formTask = $this->createFormBuilder($task)
      ->add('category', 'choice', array('label' => 'Kategorie', 'choices' => array('apis' => 'APIS', 'biblio' => 'Biblio', 'bl' => 'BL', 'ddb' => 'DDB', 'hgv' => 'HGV', 'tm' => 'TM')))
      ->add('description', 'textarea', array('label' => 'Beschreibung'))
      ->getForm();

    return $this->render('PapyrillioBeehiveBundle:Correction:show.html.twig', array('correction' => $this->correction, 'logs' => $this->logs, 'formTask' => $formTask->createView()));
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
