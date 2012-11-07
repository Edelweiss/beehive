<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Edition;
use Papyrillio\BeehiveBundle\Entity\Task;
use Papyrillio\BeehiveBundle\Entity\IndexEntry;
use DateTime;

class CorrectionController extends BeehiveController{
  protected $entityManager = null;
  protected $repository = null;
  protected $correction = null;
  protected $logs = null;

  public function listAction($print = false){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    $corrections = array();

    if ($this->getRequest()->getMethod() == 'POST') {
      
      // REQUEST PARAMETERS
      
      $limit         = $this->getParameter('rows');
      $page          = $this->getParameter('page');
      $offset        = $page * $limit - $limit;
      $offset        = $offset < 0 ? 0 : $offset;
      $sort          = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');
      $visible       = explode(';', rtrim($this->getParameter('visible'), ';'));

      // SELECT

      $visibleColumns = array('title');
      foreach($visible as $column){
        if($column != ''){
          $visibleColumns[] = $column;
        }
      }
      $visible = $visibleColumns;

      $this->get('logger')->info('visible: ' . print_r($visible, true));
      $this->get('logger')->info('visible: ' . $this->getParameter('visible'));

      // ODER BY

      $orderBy = '';
      if(in_array($sort, array('tm', 'hgv', 'ddb', 'source', 'text', 'position', 'description', 'creator', 'created', 'status', 'compilationPage'))){
        $orderBy = ' ORDER BY c.' . $sort . ' ' . $sortDirection;
      }
      if($sort == 'edition'){
        $orderBy = ' ORDER BY e.sort, e.title ' . $sortDirection;
      }
      if($sort == 'compilation'){
        $orderBy = ' ORDER BY c2.volume ' . $sortDirection;
      }

      // WHERE WITH
      
      $where = '';
      $with = '';
      $parameters = array();
      if($this->getParameter('_search') == 'true'){
        $prefix = ' WHERE ';

        foreach(array('tm', 'hgv', 'ddb', 'source', 'text', 'position', 'description', 'creator', 'created', 'status') as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'c.' . $field . ' LIKE :' . $field;
            $parameters[$field] = '%' . $this->getParameter($field) . '%';
            $prefix = ' AND ';
          }
        }

        if($this->getParameter('edition')){
          $where .= $prefix . '(e.title LIKE :edition OR e.sort LIKE :edition)';
          $parameters['edition'] = '%' . $this->getParameter('edition') . '%';
          $prefix = ' AND ';
        }

        if($this->getParameter('compilation')){
          $where .= $prefix . '(c2.title LIKE :compilation OR c2.volume LIKE :compilation)';
          $parameters['compilation'] = '%' . $this->getParameter('compilation') . '%';
          $prefix = ' AND ';
        }

        $prefix = ' WITH ';
        foreach(array('task_bl', 'task_tm', 'task_hgv', 'task_ddb', 'task_apis', 'task_biblio') as $field){
          if(strlen($this->getParameter($field))){
            $with = $prefix . ' (t.category = \'' . str_replace('task_', '', $field) . '\' AND t.description LIKE \'%' . ($this->getParameter($field) != '*' ? $this->getParameter($field) : '') . '%\')';
            //$key =  ucfirst(str_replace('task_', '', $field));
            //$with = $prefix . ' (t.category = :category' . $key . ' AND t.description LIKE :description' . $key . ')'; 
            //$parameters['category' . $key] = strtolower($field);
            //$parameters['description' . $key] = '%' . $this->getParameter($field) . '%';
            $prefix = ' OR ';
          }
        }
      }

      // LIMIT

      $query = $entityManager->createQuery('
        SELECT count(DISTINCT c.id) FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2
        ' . $with . ' ' . $where
      );
      $query->setParameters($parameters);
      $count = $query->getSingleScalarResult();
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;

      // PAGINATION
      
      if(!$print){
        $query = $entityManager->createQuery('
          SELECT DISTINCT c.id FROM PapyrillioBeehiveBundle:Correction c
          LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2
          ' . $with . ' ' . $where
        );
        $query->setParameters($parameters);
        $query->setFirstResult($offset)->setMaxResults($limit);

        $result = $query->getScalarResult();
        $ids = array();
        foreach ($result as $row) {
          $ids[] = $row['id'];
        }
        if($where === ''){
          $where = ' WHERE ';
        } else {
          $where .= ' AND ';
        }
        $where .= 'c.id IN (:id)';
        $parameters['id'] = $ids;

      }

      $this->get('logger')->info('limit: ' . $limit);
      $this->get('logger')->info('page: ' . $page);
      $this->get('logger')->info('offset: ' . $offset);
      $this->get('logger')->info('sort: ' . $sort);
      $this->get('logger')->info('sortDirection: ' . $sortDirection);
      $this->get('logger')->info('totalPages: ' . $totalPages);

      // QUERY
      
      $query = $entityManager->createQuery('
        SELECT e, c, t FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 ' . $with . ' ' . $where . ' ' . $orderBy
      );
      $query->setParameters($parameters);

      $corrections = $query->getResult();

      if($print){
        return $this->render('PapyrillioBeehiveBundle:Correction:print.html.twig', array('corrections' => $corrections, 'visible' => $visible));
      } else {
        return $this->render('PapyrillioBeehiveBundle:Correction:list.xml.twig', array('corrections' => $corrections, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page));
      }
    } else {
      if($print){
        return $this->render('PapyrillioBeehiveBundle:Correction:print.html.twig', array('corrections' => $corrections, 'visible' => array()));
      } else {
        return $this->render('PapyrillioBeehiveBundle:Correction:list.html.twig', array('corrections' => $corrections));
      }
    }
  }
  
  public function newAction(){
    $correction = new Correction();
    
    $correction->setCreator($this->get('security.context')->getToken()->getUser()->getUsername());
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $editionRepository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');

    $correction->setCompilation($this->getCompilation());
    $correction->setEdition($this->getEdition());

    $form = $this->createFormBuilder($correction)
      ->add('compilationPage', 'text')
      ->add('text', 'text', array('attr' => array('wizard-url' => $this->generateUrl('PapyrillioBeehiveBundle_numberwizardlookup'))))
      ->add('position', 'text', array('required' => false, 'label' => 'Zeile'))
      ->add('description', 'textarea', array('label' => 'Eintrag'))
      ->add('tm', 'number', array('required' => $correction->getEdition()->getSort() == 0 ? false : true, 'attr' => array('wizard-url' => $this->generateUrl('PapyrillioBeehiveBundle_numberwizard'))))
      ->add('hgv', 'text', array('required' => $correction->getEdition()->getSort() == 0 ? false : true, 'attr' => array('wizard-url' => $this->generateUrl('PapyrillioBeehiveBundle_numberwizard'))))
      ->add('ddb', 'text', array('required' => $correction->getEdition()->getSort() == 0 ? false : true, 'attr' => array('wizard-url' => $this->generateUrl('PapyrillioBeehiveBundle_numberwizard'))))
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

    return $this->render('PapyrillioBeehiveBundle:Correction:new.html.twig', array('form' => $form->createView(), 'compilations' => $this->getCompilations(), 'editions' => $editionRepository->findBy(array(), array('sort' => 'asc'))));
  }

  protected function getCompilation($id = null){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Compilation');

    if($id !== null){
      return $repository->findOneBy(array('id' => $id));
    } else if($this->getRequest()->getMethod() == 'POST'){
      return $repository->findOneBy(array('id' => $this->getParameter('compilation')));
    } else {
      return $repository->findOneBy(array('volume' => 13));
    }
  }

  protected function getCompilations(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Compilation');

    return $repository->findAll();
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
    $elementId = $this->getParameter('elementid');

    if($elementId == 'compilation'){
      $this->correction->setCompilation($this->getCompilation($this->getParameter('newvalue')));
      $this->entityManager->flush();
      return new Response(htmlspecialchars($this->correction->getCompilation()->getTitle()));
    } else {
      $setter = 'set' . ucfirst($elementId);
      $getter = 'get' . ucfirst($elementId);
      
      $this->correction->$setter($this->getParameter('newvalue'));
      $this->entityManager->flush();

      return new Response(htmlspecialchars($this->correction->$getter()));
    }
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
    $formTask = $this->getForm($task);

    $index = new IndexEntry();
    $index->setCorrection($this->correction);
    $formIndex = $this->getForm($index);

    return $this->render('PapyrillioBeehiveBundle:Correction:show.html.twig', array('correction' => $this->correction, 'compilations' => $this->getCompilations(), 'logs' => $this->logs, 'formTask' => $formTask->createView(), 'formIndex' => $formIndex->createView()));
  }
  
  public function snippetLinkAction($id) {
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');

    $correction = $repository->findOneBy(array('id' => $id));
    
    $this->get('logger')->info('********************');
    $this->get('logger')->info(print_r($correction->getLinks(), TRUE));

    return $this->render('PapyrillioBeehiveBundle:Correction:snippetLink.html.twig', array('correction' => $correction));
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

  }
}
