<?php

namespace App\Controller;

use App\Entity\Correction;
use App\Entity\Log;
use App\Entity\Compilation;
use App\Entity\Register;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use DateTime;

class ApiaryController extends BeehiveController{

  protected static $TYPES = array('tm' => 'r.tm', 'hgv' => 'r.hgv', 'ddb' => 'r.ddb', 'biblio' => 'c.source', 'bl' => 'c2.volume', 'register' => 'r.id', 'boep' => 'c2.title', 'collection' => 'c2.collection', 'volume' => 'r.ddb');

  public function index(): Response{
    return $this->render('apiary/index.html.twig');
  }

  private function getCompilationsOfInterest($corrections){
    $compilationsOfInterest = [];

    foreach($corrections as $correction){
      if($correction->getCompilation()->getCollection() != 'BOEP'){
        if(!isset($compilationsOfInterest[$correction->getCompilation()->getId()])){
          $compilationsOfInterest[$correction->getCompilation()->getId()] = $correction->getCompilation();
        }
      } else {
        $compilationsOfInterest['BOEP'] = $correction->getCompilation();
      }
    }
    ksort($compilationsOfInterest);
    return $compilationsOfInterest;
  }

  private function getCompilations($type, $id){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Compilation::class);
    $result = [];
    if($type === 'collection' && in_array($id, ['BL', 'BL Konk.', 'BOEP'])){
      $result = $repository->findBy(['collection' => $id], ['collection' => 'ASC', 'volume' => 'ASC']);
    } elseif ($type === 'collection' && in_array($id, ['ddb', 'dclp'])) {
      $result = $repository->findBy(['collection' => 'BOEP'], ['collection' => 'ASC', 'volume' => 'ASC']);
    } elseif ($type === 'bl') {
      $result = $repository->findBy(['collection' => 'BL'], ['collection' => 'ASC', 'volume' => 'ASC']);
    } elseif ($type === 'BL') {
      $result = $repository->findBy(['collection' => 'BL'], ['collection' => 'ASC', 'volume' => 'ASC']);
    } elseif ($type === 'boep') {
      $result = $repository->findBy(['collection' => 'BOEP'], ['collection' => 'ASC', 'volume' => 'ASC']);
    } elseif ($type === 'BOEP') {
      $result = $repository->findBy(['collection' => 'BOEP'], ['collection' => 'ASC', 'volume' => 'ASC']);
    } elseif ($type === 'BL Konk.') {
      $result = $repository->findBy(['collection' => 'BL Konk.'], ['collection' => 'ASC', 'volume' => 'ASC']);
    } else {
      $result = $repository->findBy([], ['collection' => 'ASC', 'volume' => 'ASC']);
    }
    return $result;
  }

  private function makeTitle ($type, $id, $corrections){
    if($type === 'boep'){
      return $id;
    }
    if($type === 'bl'){
      if($id == 2){
        return 'BL II 1 + 2';
      }
      if(count($corrections)){
        return $corrections[0]->getCompilation()->getShort();
      }
    }
    if(\in_array($type, ['tm', 'hgv', 'ddb', 'biblio', 'volume', 'register'])){
      return strtoupper($type) . ' ' . $id;
    }
    if($type === 'collection'){
      if($id ===  'ddb') {
        return 'DDB-Einträge in BOEP';
      } elseif ($id ===  'dclp') {
        return 'DCLP-Einträge in BOEP';
      } elseif ($id ===  'BL') {
        return 'Berichtigungsliste Online';
      } elseif ($id ===  'BL Konk.') {
        return 'Konkordanz der Berichtigungsliste Online';
      } elseif ($id ===  'BOEP') {
        return 'Bulletin of Online Emendations to Papyri';
      }
    }
    return $id;
  }
  
  public function info($id): Response{

    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);

    $correction = $repository->findOneBy(['id' => $id]);
    
    if(!$correction){
      throw $this->createNotFoundException('Correction #' . $id . ' does not exist');
    }

    $logs = array_merge(
      $entityManager->getRepository(Log::class)->getLogs($correction),
      $entityManager->getRepository(Log::class)->getTaskLogs($correction));

    return $this->render('apiary/info.html.twig', ['correction' => $correction, 'logs' => $logs]);
  }

  public function honey($type, $id, $format = 'html'): Response{
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);

    // WHERE

    $where = self::$TYPES[$type] . ' = :id';
    if($type === 'boep' || $type === 'volume'){
      if($type === 'volume' && !str_ends_with($id, ';')){
        $id .= ';';
      }
      $where = self::$TYPES[$type] . ' LIKE :id';
      $id .= '%';
    }
    $parameters = array('id' => $id);

    // SORT

    $sort = 'c.sort';
    if(($type === 'collection' && $id === 'BOEP') || ($type === 'boep' && $id === 'Bulletin of Online Emendations to Papyri')){
      $sort = 'c.sort';
    } elseif ($type === 'boep' && preg_match('/Bulletin of Online Emendations to Papyri [\d.]+/', $id)) {
      $sort = 'c.compilationIndex';
    }

    // QUERY

    $query = $entityManager->createQuery('
      SELECT e, c, t, r, d FROM App\Entity\Correction c
      LEFT JOIN c.registerEntries r LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 LEFT JOIN e.docketEntries d WHERE ' . $where . ' ORDER BY ' . $sort
    );
    $query->setParameters($parameters);
    if($type === 'collection' && in_array($id, ['ddb', 'dclp'])){
      $query = $entityManager->createQuery('
        SELECT e, c, t, r FROM App\Entity\Correction c
        JOIN c.registerEntries r LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 WHERE c2.collection = :collection AND  r.' . $id . ' IS NOT NULL ORDER BY c.sort'
      );
      $query->setParameters(['collection' => 'BOEP']);
      $query->setMaxResults(2000);
    }

    $corrections = $query->getResult();
    $compilations = $this->getCompilations($type, $id);
    $compilationsOfInterest = $this->getCompilationsOfInterest($corrections);
    $title = $this->makeTitle($type, trim($id, '%'), $corrections);

    if($format === 'html'){
      return $this->render('apiary/honey.html.twig', ['corrections' => $corrections, 'compilations' => $compilations, 'compilationsOfInterest' => $compilationsOfInterest, 'title' => $title, 'type' => $type, 'id' => trim($id, '%')]);
    } elseif($format === 'plain'){
      return $this->render('apiary/snippetHoney.html.twig', ['corrections' => $corrections, 'compilations' => $compilations, 'compilationsOfInterest' => $compilationsOfInterest, 'title' => $title, 'type' => $type, 'id' => trim($id, '%')]);
    } elseif ($format === 'rdf') {
      $response = new Response($this->renderView('apiary/honey.xml.twig', ['corrections' => $corrections, 'compilations' => $compilations, 'compilationsOfInterest' => $compilationsOfInterest, 'title' => $title, 'type' => $type, 'id' => trim($id, '%')]));
      $response->headers->set('Content-Type', 'application/rdf+xml'); //$response->headers->set('Content-Type', 'text/xml');
      return $response;
    } elseif ($format === 'latex') {
      $response = new Response($this->renderView('apiary/honey.tex.twig', ['corrections' => $corrections, 'compilations' => $compilations, 'compilationsOfInterest' => $compilationsOfInterest, 'title' => $title, 'type' => $type, 'id' => trim($id, '%')]));
      $response->headers->set('Content-Type', 'application/tex+txt'); //$response->headers->set('Content-Type', 'text/tex');
      $response->headers->set('Content-Disposition', 'attachment; filename=' . str_replace(' ', '', $title . '.tex'));
      return $response;
    } else {
      $data = ['corrections' => []];
      $data = $data['count'] = count($corrections);
      foreach($corrections as $correction){
        $data['corrections'][$correction->getId()] = array(
          'tm' => $correction->getTm(),
          'ddb' => $correction->getDdb(),
          'hgv' => $correction->getHgv(),
          'description' => $correction->getDescription(),
          'status' => $correction->getStatus()
        );
      }
      return new Response(json_encode(array('success' => 'true', 'data' => $data)));
    }
  }

}
