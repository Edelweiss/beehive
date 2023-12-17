<?php

namespace App\Controller;

use App\Entity\Correction;
use App\Entity\Compilation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use DateTime;

class ApiaryController extends BeehiveController{

  protected static $TYPES = array('tm' => 'r.tm', 'hgv' => 'r.hgv', 'ddb' => 'r.ddb', 'biblio' => 'c.source', 'bl' => 'c2.volume', 'register' => 'r.id', 'boep' => 'c2.title', 'collection' => 'c2.collection', 'volume' => 'r.ddb');

  public function index(): Response{
    return $this->render('apiary/index.html.twig');
  }

  private function getCompilations($type, $id){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Compilation::class);
    $result = [];
    if($type === 'collection'){
      $result = $repository->findBy(['collection' => $id], ['collection' => 'DESC', 'volume' => 'ASC']);
    } elseif ($type === 'BL') {
      $result = $repository->findBy(['collection' => 'BL'], ['collection' => 'DESC', 'volume' => 'ASC']);
    } elseif ($type === 'BOEP') {
      $result = $repository->findBy(['collection' => 'BOEP'], ['collection' => 'DESC', 'volume' => 'ASC']);
    } elseif ($type === 'BL Konk.') {
      $result = $repository->findBy(['collection' => 'BL Konk.'], ['collection' => 'DESC', 'volume' => 'ASC']);
    } else {
      $result = $repository->findBy([], ['volume' => 'ASC']);
    }
    return $result;
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

    if($this->isGranted('ROLE_USER') === false) {
      $where .= ' AND (c.status = :status OR c2.title = :compilationTitle)'; // cl: hardcoded BLXII hack, show unchecked BLXII corrections
      $parameters['status'] = 'finalised';
      $parameters['compilationTitle'] = 'XII';
    }
  
    // SORT
    $sort = 'c.sort';
    if(($type === 'collection' && $id === 'BOEP') || ($type === 'boep' && $id === 'Bulletin of Online Emendations to Papyri')){
      $sort = 'c.sort';
    } elseif ($type === 'boep' && preg_match('/Bulletin of Online Emendations to Papyri [\d.]+/', $id)) {
      $sort = 'c.compilationIndex';
    }

    // QUERY

    $query = $entityManager->createQuery('
      SELECT e, c, t FROM App\Entity\Correction c
      LEFT JOIN c.registerEntries r LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 WHERE ' . $where . ' ORDER BY ' . $sort
    );

    $query->setParameters($parameters);

    $corrections = $query->getResult();
    $compilations = $this->getCompilations($type, $id);

    if($format === 'html'){
      return $this->render('apiary/honey.html.twig', ['corrections' => $corrections, 'compilations' => $compilations]);
    } elseif($format === 'plain'){
      return $this->render('apiary/snippetHoney.html.twig', ['corrections' => $corrections, 'compilations' => $compilations]);
    } elseif ($format === 'rdf') {
      $response = new Response($this->renderView('apiary/honey.xml.twig', ['corrections' => $corrections, 'compilations' => $compilations]));
      $response->headers->set('Content-Type', 'application/rdf+xml'); //$response->headers->set('Content-Type', 'text/xml');
      return $response;
    } else {
      $data = array('corrections' => array());
      $data = array('count' => count($corrections));
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
