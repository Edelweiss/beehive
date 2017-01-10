<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use DateTime;

class ReportController extends BeehiveController{

  public function leidenAction($compilationVolume = 13){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');

    $query = $entityManager->createQuery('
      SELECT e, c, t FROM PapyrillioBeehiveBundle:Correction c
      LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 WHERE c2.volume = :compilationVolume ORDER BY e.sort, c.sort'
    );
    $query->setParameters(array('compilationVolume' => $compilationVolume));

    $corrections = $query->getResult();

    $compilation = new Compilation();
    if(count($corrections)){
      $compilation = current($corrections)->getCompilation();
    }

    return $this->render('PapyrillioBeehiveBundle:Report:leiden.html.twig', array('compilation' => $compilation, 'corrections' => $corrections));
  }

  public function printAction($compilationVolume = 13, $editionId){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    
    $where = 'WHERE c2.volume = :compilationVolume';
    $parameters = array('compilationVolume' => $compilationVolume);
    
    if($editionId){
      $where .= ' AND e.id = :editionId';
      $parameters['editionId'] = $editionId;
    }

    $query = $entityManager->createQuery('
      SELECT e, c, t FROM PapyrillioBeehiveBundle:Correction c
      LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 ' . $where . ' ORDER BY e.sort, c.sort'
    );
    $query->setParameters($parameters);
    $query->setFirstResult(0)->setMaxResults(100);

    $corrections = $query->getResult();

    $compilation = new Compilation();
    if(count($corrections)){
      $compilation = current($corrections)->getCompilation();
    } else if ($compilationVolume) {
      $repositoryCompilation = $entityManager->getRepository('PapyrillioBeehiveBundle:Compilation');
      $compilation = $repositoryCompilation->findOneBy(array('volume' => $compilationVolume));
    }

    $correctionsGroupedByEdition = array();
    $currentText = '';
    foreach($corrections as $correction){
      if($correction->getText() != $currentText){
        $currentText = $correction->getText();
      } else if ($correctionsGroupedByEdition[$correction->getEdition()->getId()]){
        $correction->setText('');
      }
      $correctionsGroupedByEdition[$correction->getEdition()->getId()][] = $correction;
    }

    return $this->render('PapyrillioBeehiveBundle:Report:print.html.twig', array('compilation' => $compilation, 'corrections' => $corrections, 'editions' => $correctionsGroupedByEdition));
  }

  public function leidenSnippetAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');

    $corrections = $repository->findBy(array('id' => $id));

    return $this->render('PapyrillioBeehiveBundle:Report:leidenSnippet.html.twig', array('corrections' => $corrections));
  }
}
