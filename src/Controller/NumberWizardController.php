<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Correction;
use DOMDocument;
use DOMXPath;

class NumberWizardController extends BeehiveController{
  
  public function autocompleteDdb(): Response {
    $term = $this->getParameter('term');
    
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);

    $query = $entityManager->createQuery('SELECT DISTINCT c.ddb FROM App\Entity\Correction c WHERE c.ddb LIKE \'' . $term . '%\' ORDER BY c.ddb');

    $autocomplete = array();
    foreach($query->getResult() as $result){
      $autocomplete[] = $result['ddb'];
    }

    return new Response(json_encode($autocomplete));
  }
  
  public function index(): Response {
    $id = $this->getParameter('id');
    $data = $this->getData($id);

    return new Response(json_encode(array('success' => true, 'data' => $data)));
  }
  
  public function lookup(): Response {
    $text = $this->getParameter('text');
    $editionId = $this->getParameter('editionId');
    $data = $this->getData();

    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);

    $query = $entityManager->createQuery('SELECT c FROM App\Entity\Correction c WHERE c.edition = :edition AND c.text = :text')->setParameters(array('text' => $text, 'edition' => $editionId))->setMaxResults(1);
    $corrections = $query->getResult();

    if(count($corrections)){
      $correction = $corrections[0];
      $data = $this->getData($correction->getHgv());
    }

    return new Response(json_encode(array('success' => true, 'data' => $data)));
  }

  protected function getData($id = 0){
    $data = array('tm' => array(), 'hgv' => array(), 'ddb' => array(), 'bl' => array());

    if(!$id){
      return $data;
    }

    // TM, HGV, DDB

    $doc = new DOMDocument;
    $idnoFile = \dirname(__DIR__). '/../data/idno.xml'; # cl: HACK $this->getParameter('kernel.project_dir') . '/../data/idno.xml'
    $this->logger->info('XML FILE ' . $idnoFile);
    $this->logger->info(file_exists($idnoFile));
    $doc->load($idnoFile); # LIBXML_NOWARNING
    $xpath = new DOMXPath($doc);

    if($this->isHgv($id)){
      $data['hgv'] = array($id);
      $data['tm'] = array(preg_replace('/[^\d]+/', '', $id));

      $query = '/list/item/idno[@type = "hgv"][text() = "' . $id . '"]/ancestor::item/idno[@type = "ddb"]';
      $result = $xpath->query($query);

      if($result->length){
        $data['ddb'] = array($result->item(0)->textContent);
      }

    } else if($this->isDdb($id)){
      $data['ddb'] = array($id);

      $query = '/list/item/idno[@type = "ddb"][text() = "' . $id . '"]/ancestor::item/idno[@type = "hgv"]';
      $result = $xpath->query($query);

      if($result->length){
        $data['hgv'] = array($result->item(0)->textContent);
        $data['tm'] = array(preg_replace('/[^\d]+/', '', $result->item(0)->textContent));
      }
    }

    // BL EDITION & TEXT

    if(count($data['hgv']) and preg_match('/^\d+[A-Za-z]*$/', $data['hgv'][0])){
      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(Correction::class);
      
      $query = $entityManager->createQuery('SELECT c FROM App\Entity\Correction c LEFT JOIN c.registerEntries r WHERE r.hgv = \'' . $data['hgv'][0] . '\'')->setMaxResults(1);
      $corrections = $query->getResult();
      
      if(count($corrections)){
        $correction = $corrections[0];
        $data['bl'] = array('edition' => $correction->getEdition()->getId(), 'text' => $correction->getText());
      }
    }

    return $data;

  }
  
  public function _index(): Response { // old version using number server rdf

    $id = $this->getParameter('id');
    $data = array('tm' => array(), 'hgv' => array(), 'ddb' => array(), 'bl' => array());
    
    if($this->isHgv($id)){
      $data['tm'] = array(preg_replace('/[^\d]+/', '', $id));
      $data['hgv'] = array($id);
    } else if($this->isDdb($id)){
      $data['ddb'] = array($id);
    }

    $path = 'http://papyri.info/' . ( $this->isDdb($id) ? 'ddbdp' : 'hgv') . '/' . $id . '/rdf';
    $rdf = file_get_contents($path);

    $doc = new DOMDocument;
    $doc->load($path);
    $xpath = new DOMXPath($doc);
    $query = '//dcterms:relation/@rdf:resource';
    $relations = $xpath->query($query);

    $matches = array();
    foreach($relations as $relation){

      if(preg_match('/^http:\/\/papyri\.info\/ddbdp\/(.+)\/source$/', $relation->value, $matches)){
        if(!in_array($matches[1], $data['ddb'])){
          if(!$this->isReprinted($matches[1])){
            $data['ddb'][] = $matches[1];
          }
        }
      }
      if(preg_match('/^http:\/\/papyri\.info\/hgv\/(.+)\/source$/', $relation->value, $matches)){
        if(!in_array($matches[1], $data['hgv'])){
          $data['hgv'][] = $matches[1];
        }
      }
      if(preg_match('/^http:\/\/www\.trismegistos\.org\/tm\/detail\.php\?quick=(\d+)$/', $relation->value, $matches)){
        if(!in_array($matches[1], $data['tm'])){
          $data['tm'][] = $matches[1];
        }
      }
    }

    return new Response(json_encode(array('success' => true, 'data' => $data)));
  }

  protected function isDdb($id){
    return preg_match('/^.+;.*;.+$/', $id);
  }

  protected function isHgv($id){
    return !$this->isDdb($id);
  }
  
  protected function isReprinted($id){
    return false;
  }
}
