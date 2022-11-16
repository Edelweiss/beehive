<?php

namespace App\Controller;

use App\Entity\Correction;
use App\Entity\Compilation;
use App\Entity\Edition;
use App\Entity\Task;
use App\Entity\IndexEntry;
use App\Entity\Register;
use App\Entity\Log;
use App\Form\CorrectionNewType;
use App\Form\TaskType;
use App\Form\IndexEntryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CorrectionController extends BeehiveController{
  protected $entityManager = null;
  protected $repository = null;
  protected $correction = null;
  protected $logs = null;

  public function list($print = false): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);
    $corrections = [];
    if ($this->request->getMethod() == 'POST') {
      
      // REQUEST PARAMETERS
      
      $limit         = $this->getParameter('rows');
      $page          = $this->getParameter('page');
      $offset        = $page * $limit - $limit;
      $offset        = $offset < 0 ? 0 : $offset;
      $sort          = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');
      $visible       = explode(';', rtrim($this->getParameter('visible'), ';'));

      // SELECT

      $visibleColumns = ['title'];
      foreach($visible as $column){
        if($column != ''){
          $visibleColumns[] = $column;
        }
      }
      $visible = $visibleColumns;

      $this->logger->info('visible: ' . print_r($visible, true));
      $this->logger->info('visible: ' . $this->getParameter('visible'));

      // ODER BY

      $orderBy = '';
      if(in_array($sort, ['source', 'text', 'position', 'description', 'creator', 'created', 'status', 'compilationPage'])){
        $orderBy = ' ORDER BY c.' . $sort . ' ' . $sortDirection;
      }
      if(in_array($sort, ['tm', 'hgv', 'ddb'])){
        $orderBy = ' ORDER BY r.' . $sort . ' ' . $sortDirection;
      }
      if($sort == 'edition'){
        $orderBy = ' ORDER BY e.sort ' . $sortDirection .  ', e.title ' . $sortDirection;
      }
      if($sort == 'compilation'){
        $orderBy = ' ORDER BY c2.volume ' . $sortDirection . ', c2.fascicle ' . $sortDirection;
      }

      // WHERE WITH

      $where = '';
      $with = '';
      $parameters = [];
      if($this->getParameter('_search') == 'true'){
        $prefix = ' WHERE ';

        foreach(['source', 'text', 'position', 'description', 'creator', 'created', 'status', 'compilationPage'] as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'c.' . $field . ' LIKE :' . $field;
            $parameters[$field] = '%' . $this->getParameter($field) . '%';
            $prefix = ' AND ';
          }
        }

        foreach(['tm', 'hgv', 'ddb'] as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'r.' . $field . ' LIKE :' . $field;
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
          $where .= $prefix . '(c2.title = :compilation OR c2.volume = :compilation)';
          $parameters['compilation'] = $this->getParameter('compilation');
          $prefix = ' AND ';
        }

        $prefix = ' WITH ';
        foreach(['task_bl', 'task_tm', 'task_hgv', 'task_ddb', 'task_apis', 'task_biblio'] as $field){
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
        SELECT count(DISTINCT c.id) FROM App\Entity\Correction c
        LEFT JOIN c.registerEntries r LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2
        ' . $with . ' ' . $where
      );
      $query->setParameters($parameters);
      $count = $query->getSingleScalarResult();
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;

      // PAGINATION

      if(!$print){
        $query = $entityManager->createQuery('
          SELECT DISTINCT c.id FROM App\Entity\Correction c
          LEFT JOIN c.registerEntries r LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2
          ' . $with . ' ' . $where . ' ' . $orderBy
        );
        $query->setParameters($parameters);
        $query->setFirstResult($offset)->setMaxResults($limit);

        $result = $query->getScalarResult();
        $ids = [];
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

      $this->logger->info('limit: ' . $limit);
      $this->logger->info('page: ' . $page);
      $this->logger->info('offset: ' . $offset);
      $this->logger->info('sort: ' . $sort);
      $this->logger->info('sortDirection: ' . $sortDirection);
      $this->logger->info('totalPages: ' . $totalPages);

      // QUERY

      $query = $entityManager->createQuery('
        SELECT e, c, t FROM App\Entity\Correction c
        LEFT JOIN c.registerEntries r LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 ' . $with . ' ' . $where . ' ' . $orderBy
      );
      $query->setParameters($parameters);

      $corrections = $query->getResult();

      if($print){
        return $this->render('correction/print.html.twig', ['corrections' => $corrections, 'visible' => $visible]);
      } else {
        return $this->render('correction/list.xml.twig', ['corrections' => $corrections, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page]);
      }
    } else {
      if($print){
        return $this->render('correction/print.html.twig', ['corrections' => $corrections, 'visible' => []]);
      } else {
        return $this->render('correction/list.html.twig', ['corrections' => $corrections]);
      }
    }
  }

  public function new(): Response {
    $correction = new Correction();

    $correction->setCreator($this->getUser()->getUsername());
    // $this->get('security.context')->getToken()->getUser()->getUsername()

    $entityManager = $this->getDoctrine()->getManager();
    $editionRepository = $entityManager->getRepository(Edition::class);

    $correction->setCompilation($this->getCompilation());
    $correction->setEdition($this->getEdition());

    $registerRepository = $entityManager->getRepository(Register::class);

    $form = $this->createForm(CorrectionNewType::class, $correction, ['attr' => ['wizardUrl' => $this->generateUrl('PapyrillioBeehive_NumberWizardLookup')]]);

    if ($this->request->getMethod() == 'POST') {
      $form->handleRequest($this->request);
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

        if($this->getParameter('register')){
          foreach($this->getParameter('register') as $registerId){
            $register = $registerRepository->findOneBy(['id' => $registerId]);
            if($register){
              $correction->addRegisterEntry($register);
            }
          }
        }
        $entityManager->persist($correction);
        $entityManager->flush();

        if($this->getParameter('redirectTarget') === 'new'){
          $this->addFlash('notice', 'Der Datensatz wurde angelegt!');
          return $this->redirect($this->generateUrl('PapyrillioBeehive_CorrectionNew'));
        } else {
          return $this->redirect($this->generateUrl('PapyrillioBeehive_CorrectionShow', ['id' => $correction->getId()]));
        }
      }
    }

    return $this->render('correction/new.html.twig', ['form' => $form->createView(), 'compilations' => $this->getCompilations(), 'editions' => $editionRepository->findBy([], ['sort' => 'asc'])]);
  }

  protected function getCompilation($id = null){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Compilation::class);

    if($id !== null){
      return $repository->findOneBy(['id' => $id]);
    } else if($this->request->getMethod() == 'POST'){
      return $repository->findOneBy(['id' => $this->getParameter('compilation')]);
    } else {
      return $repository->findOneBy(['volume' => 14]);
    }
  }

  protected function getCompilations(){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Compilation::class);

    return $repository->findAll();
  }

  protected function getEdition(){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Edition::class);

    if($this->request->getMethod() == 'POST'){
      return $repository->findOneBy(['id' => $this->getParameter('edition')]);
    }else{
      return $repository->findOneBy(['sort' => 0]);
    }
  }

  public function update($id): Response {
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
      $this->entityManager->refresh($this->correction);
      return new Response(htmlspecialchars($this->correction->$getter()));
    }
  }

  public function delete($id): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);
    $correction = $repository->findOneBy(['id' => $id]);
    foreach($correction->getTasks() as $task){
      $entityManager->remove($task);
    }
    foreach($correction->getIndexEntries() as $indexEntry){
      $entityManager->remove($indexEntry);
    }

    $entityManager->remove($correction);
    $entityManager->flush();
    return $this->redirect($this->generateUrl('PapyrillioBeehive_CorrectionList'));
  }

  public function show($id): Response {

    if(!$id){
      return $this->forward('PapyrillioBeehiveBundle:Correction:list');
    }

    $this->retrieveCorrection($id);

    $task = new Task();
    $task->setCorrection($this->correction);
    $formTask = $this->createForm(TaskType::class, $task);

    $index = new IndexEntry();
    $index->setCorrection($this->correction);
    $formIndex = $this->createForm(IndexEntryType::class, $index);
    

    return $this->render('correction/show.html.twig', ['correction' => $this->correction, 'compilations' => $this->getCompilations(), 'logs' => $this->logs, 'formTask' => $formTask->createView(), 'formIndex' => $formIndex->createView()]);
  }
  
  public function snippetLink($id): Response {
    $this->retrieveCorrection($id);
    
    $this->logger->info('********************');
    $this->logger->info(print_r($this->correction->getLinks(), TRUE));

    return $this->render('correction/snippetLink.html.twig', ['correction' => $this->correction]);
  }

  protected function retrieveCorrection($id){
    $this->entityManager = $this->getDoctrine()->getManager();
    $this->repository = $this->entityManager->getRepository(Correction::class);

    $this->correction = $this->repository->findOneBy(['id' => $id]);
    
    if(!$this->correction){
      throw $this->createNotFoundException('Correction #' . $id . ' does not exist');
    }

    $this->logs = array_merge(
      $this->entityManager->getRepository(Log::class)->getLogs($this->correction),
      $this->entityManager->getRepository(Log::class)->getTaskLogs($this->correction));
  }
}
