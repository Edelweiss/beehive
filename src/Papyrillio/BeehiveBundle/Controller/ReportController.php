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
    $correctionRepository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    $compilationRepository = $entityManager->getRepository('PapyrillioBeehiveBundle:Compilation');

    $compilation = $compilationRepository->findOneBy(array('volume' => $compilationVolume));
    if(!$compilation){
      $compilation = $compilationRepository->findOneBy(array('volume' => 13));
    }

    $corrections = $compilation->getCorrections();

    return $this->render('PapyrillioBeehiveBundle:Report:leiden.html.twig', array('compilation' => $compilation, 'corrections' => $corrections));
  }

  public function leidenSnippetAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');

    $corrections = $repository->findBy(array('id' => $id));

    return $this->render('PapyrillioBeehiveBundle:Report:leidenSnippet.html.twig', array('corrections' => $corrections));
  }
}
