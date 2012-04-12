<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use DOMDocument;
use DOMXPath;
class NumberWizardController extends BeehiveController{
  
  public function autocompleteDdbAction(){
    $term = $this->getParameter('term');
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    
    $query = $entityManager->createQuery('SELECT DISTINCT c.ddb FROM PapyrillioBeehiveBundle:Correction c WHERE c.ddb LIKE \'' . $term . '%\' ORDER BY c.ddb');

    
    $autocomplete = array();
    foreach($query->getResult() as $result){
      $autocomplete[] = $result['ddb'];
    }
    
    return new Response(json_encode($autocomplete));
  }
  
  public function indexAction(){
    $id = $this->getParameter('id');
    $data = $this->getData($id);

    return new Response(json_encode(array('success' => true, 'data' => $data)));
  }
  
  public function lookupAction(){
    $text = $this->getParameter('id');
    $data = $this->getData();

    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    
    $query = $entityManager->createQuery('SELECT c FROM PapyrillioBeehiveBundle:Correction c WHERE c.text = \'' . $text . '\'')->setMaxResults(1);
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
    $doc->load($this->get('kernel')->getRootDir() . '/../src/Papyrillio/BeehiveBundle/Resources/data/idno.xml');
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

      $entityManager = $this->getDoctrine()->getEntityManager();
      $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
      
      $query = $entityManager->createQuery('SELECT c FROM PapyrillioBeehiveBundle:Correction c WHERE c.hgv = \'' . $data['hgv'][0] . '\'')->setMaxResults(1);
      $corrections = $query->getResult();
      
      if(count($corrections)){
        $correction = $corrections[0];
        $data['bl'] = array('edition' => $correction->getEdition()->getId(), 'text' => $correction->getText());
      }
    }

    return $data;

  }
  
  public function _indexAction(){ // old version using number server rdf

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
