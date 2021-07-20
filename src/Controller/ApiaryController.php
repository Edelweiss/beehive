<?php

namespace App\Controller;

use App\Entity\Correction;
use App\Entity\Compilation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use DateTime;

class ApiaryController extends BeehiveController{

  protected static $TYPES = array('tm' => 'r.tm', 'hgv' => 'r.hgv', 'ddb' => 'r.ddb', 'biblio' => 'c.source', 'bl' => 'c2.volume', 'register' => 'r.id');

  public function index(): Response{
    return $this->render('apiary/index.html.twig');
  }

  public function honey($type, $id, $format = 'html'): Response{
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);

    // WHERE

    $where = self::$TYPES[$type] . ' = :id';
    $parameters = array('id' => $id);

    if($this->isGranted('ROLE_USER') === false) {
      $where .= ' AND (c.status = :status OR c2.title = :compilationTitle)'; // cl: hardcoded BLXII hack, show unchecked BLXII corrections
      $parameters['status'] = 'finalised';
      $parameters['compilationTitle'] = 'XII';
    }

    // QUERY

    $query = $entityManager->createQuery('
      SELECT e, c, t FROM App\Entity\Correction c
      LEFT JOIN c.registerEntries r LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 WHERE ' . $where . ' ORDER BY c.sort'
    );

    $query->setParameters($parameters);

    $corrections = $query->getResult();

    if($format === 'html'){
      return $this->render('apiary/honey.html.twig', ['corrections' => $corrections]);
    } elseif($format === 'plain'){
      return $this->render('apiary/snippetHoney.html.twig', ['corrections' => $corrections]);
    } elseif ($format === 'rdf') {
      $response = new Response($this->renderView('apiary/honey.xml.twig', array('corrections' => $corrections)));
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
