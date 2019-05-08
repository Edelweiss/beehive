<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Register;
use DOMDocument;
use DOMXPath;

class RegisterController extends BeehiveController{

  public function autocompleteAction($id = 0){
    $term = $this->getParameter('term');
    $autocomplete = array();

    if(strlen($term)){
      $entityManager = $this->getDoctrine()->getEntityManager();
      $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Register');

      $search = 'r.ddb LIKE \'%' . $term . '%\'';
      $order = 'r.collection, r.volume, r.document';
      if(preg_match('/^\d/', $term)){
        $search = 'r.hgv LIKE \'' . $term . '%\' OR r.tm LIKE \'' . $term . '%\'';
        $order = 'r.tm, r.hgv';
      }

      $query = $entityManager->createQuery('SELECT r.id, r.ddb, r.tm, r.hgv FROM PapyrillioBeehiveBundle:Register r WHERE ' . $search . ' ORDER BY ' . $order);
      $query->setMaxResults(20);

      foreach($query->getResult() as $result){
        $caption = $this->makeCaption($result, (preg_match('/^\d/', $term)) ? 'hgv' : 'ddb');
        
        $autocomplete[] = array('id' => $result['id'], 'value' => $caption, 'label' => $caption);
      }
    }

    return new Response(json_encode($autocomplete));
  }

  protected function makeCaption($result, $type = 'ddb'){
    if($type == 'hgv'){
      $caption = ($result['tm'] ? $result['tm'] . ($result['hgv'] && ($result['hgv'] != $result['tm']) ? ' (' . str_replace($result['tm'], '', $result['hgv']) . ')' : '') : $result['hgv']);
      $caption .= ($result['ddb'] ? ' (' . $result['ddb'] . ')' : '');
      return $caption;
    }
    $caption = ($result['ddb'] ? $result['ddb'] . '' : '');
    $caption .= ($result['hgv'] || $result['tm'] ? ' TM/HGV ' : '');
    $caption .= ($result['tm'] ? $result['tm'] . ($result['hgv'] && ($result['hgv'] != $result['tm']) ? ' (' . str_replace($result['tm'], '', $result['hgv']) . ')' : '') : $result['hgv']);
    return $caption;
  }

  public function showAssignmentsAction($correctionId){
    $correction = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Correction')->findOneBy(array('id' => $correctionId));

    return $this->render('PapyrillioBeehiveBundle:Register:snippetFolder.html.twig', array('correction' => $correction));
  }

  public function assignAction($registerId, $correctionId){
    $register = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Register')->findOneBy(array('id' => $registerId));
    $correction = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Correction')->findOneBy(array('id' => $correctionId));

    $correction->addRegisterEntry($register);
    $this->getDoctrine()->getEntityManager()->persist($correction);
    $this->getDoctrine()->getEntityManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_registershowassignments', array('correctionId' => $correctionId)));
  }

  public function revokeAssignmentAction($registerId, $correctionId){
    $register = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Register')->findOneBy(array('id' => $registerId));
    $correction = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Correction')->findOneBy(array('id' => $correctionId));

    $register->getCorrections()->removeElement($correction);
    $correction->getRegisterEntries()->removeElement($register);

    $this->getDoctrine()->getEntityManager()->persist($correction);
    $this->getDoctrine()->getEntityManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_registershowassignments', array('correctionId' => $correctionId)));
  }

  public function createAndAssignAction($correctionId){
    $newEntry = $this->getParameter('newEntry');

    $ddb = null;
    $tm  = null;
    $hgv = null;

    $ddbParse = preg_replace('/^(.*[^\w\d])?([\w\d]+;[\w\d\.()]*;[\w\d_,+\-\/\.()]+)([^\w\d_].*)?$/', '$2', $newEntry);
    if(preg_match('/^[\w\d]+;[\w\d\.()]*;[\w\d_,+\-\/\.()]+$/', $ddbParse)){
      $ddb = $ddbParse;
    }

    $tmParse = preg_replace('/^(.* )?(\d+)( .*)?$/', '$2', $newEntry);
    if(preg_match('/^\d+$/', $tmParse)){
      $tm = $tmParse;
    }

    $hgvParse = preg_replace('/^(.* )?(\d+[a-z]+)( .*)?$/', '$2', $newEntry);
    if(preg_match('/^\d+[a-z]+$/', $hgvParse)){
      $hgv = $hgvParse;
    }
    
    if(!$hgv && $tm && preg_match('/.*' . $tm . '.+' . $tm . '.*/', $newEntry)){
      $hgv = $tm;
    }

    $register = $this->getOrCreate($ddb, $tm, $hgv);

    $correction = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Correction')->findOneBy(array('id' => $correctionId));

    $register->addCorrection($correction);
    $correction->addRegisterEntry($register);

    $this->getDoctrine()->getEntityManager()->persist($correction);
    $this->getDoctrine()->getEntityManager()->persist($register);
    $this->getDoctrine()->getEntityManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_registershowassignments', array('correctionId' => $correctionId)));
  }

  public function createAction(){
    $register = $this->getOrCreate($this->getParameter('ddb'), $this->getParameter('tm'), $this->getParameter('hgv'));

    return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_registershow', array('id' => $register->getId())));
  }

  public function showAction($id){
    $register = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Register')->findOneBy(array('id' => $id));

    return $this->render('PapyrillioBeehiveBundle:Register:show.html.twig', array('register' => $register));
  }
  
  private function getOrCreate($ddb = null, $tm = null, $hgv = null){
    $register = $this->getDoctrine()->getEntityManager()->getRepository('PapyrillioBeehiveBundle:Register')->findOneBy(array('ddb' => $ddb, 'tm' => $tm, 'hgv' => $hgv));

    if(!$register && ((isset($tm) && strlen($tm)) || (isset($hgv) && strlen($hgv)) || (isset($ddb) && strlen($ddb)))){
      $register = new Register();
      if($ddb && strlen($ddb)){
        $register->setDdb($ddb);
      }
      if($hgv && strlen($hgv)){
        $register->setHgv($hgv);
      }
      if($tm && strlen($tm)){
        $register->setTm($tm);
      }
      $this->getDoctrine()->getEntityManager()->persist($register);
      $this->getDoctrine()->getEntityManager()->flush();
    }

    return $register;
  }
}
