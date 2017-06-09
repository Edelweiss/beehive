<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use DateTime;

class ReportController extends BeehiveController{
  const ALLGEMEINES      = 0;
  const NACH_ALLGEMEINES = 1;
  const ALEX             = 25000;
  const NACH_ALEX        = 25001;
  const LOND             = 580000;
  const NACH_LOND        = 582001;
  const TAIT             = 1250000;
  const NACH_TAIT        = 1250001;

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
    return $this->render('PapyrillioBeehiveBundle:Report:print.html.twig', $this->getData($compilationVolume, $editionId));
  }

  public function pdfAction($compilationVolume = 13, $editionId){
    $data = $this->getData($compilationVolume, $editionId);
    $xml = array(
      self::ALLGEMEINES => '',
      self::NACH_ALLGEMEINES => '',
      self::ALEX => '',
      self::NACH_ALEX => '',
      self::LOND => '',
      self::NACH_LOND => '',
      self::TAIT => '',
      self::NACH_TAIT => '' );
    $xmlPointer = self::ALLGEMEINES;

    foreach($data['editions'] as $editionSort => $corrections){
      if($editionSort === self::ALLGEMEINES){
        $xmlPointer = self::ALLGEMEINES;
      } else if ($editionSort > self::ALLGEMEINES && $editionSort < self::ALEX) {
        $xmlPointer = self::NACH_ALLGEMEINES;
      } else if($editionSort === self::ALEX){
        $xmlPointer = self::ALEX;
      } else if ($editionSort > self::ALEX && $editionSort < self::LOND) {
        $xmlPointer = self::NACH_ALEX;
      } else if($editionSort >= self::LOND && $editionSort < self::NACH_LOND){
        $xmlPointer = self::LOND;
      } else if ($editionSort >= self::NACH_LOND && $editionSort < self::TAIT) {
        $xmlPointer = self::NACH_LOND;
      } else if($editionSort === self::TAIT){
        $xmlPointer = self::TAIT;
      } else if ($editionSort > self::TAIT) {
        $xmlPointer = self::NACH_TAIT;
      }
      //echo 'editionSort: '. $editionSort . ' - ' . $xmlPointer . "\n";

      $edition = $corrections[0]->getEdition();
      $xml[$xmlPointer] .= self::getTableHeader($edition);
      foreach($corrections as $correction){
        $xml[$xmlPointer] .= self::getTableContent($correction);
      }
    }
    //var_dump($xml);

    $oo = file_get_contents($this->get('kernel')->getRootDir() . '/../src/Papyrillio/BeehiveBundle/Resources/print/content_null.xml');
    foreach($xml as $key => $xmlSnippet){
      $oo = str_replace('<!-- ' . $key . ' -->', $xmlSnippet, $oo);
    }

    file_put_contents($this->get('kernel')->getRootDir() . '/../src/Papyrillio/BeehiveBundle/Resources/print/content.xml', $oo);

    if(0){
    exec('cd /Users/Admin/beehive.dev/src/Papyrillio/BeehiveBundle/Resources/print/ && zip bl.odt content.xml');
    $response = new Response(file_get_contents($this->get('kernel')->getRootDir() . '/../src/Papyrillio/BeehiveBundle/Resources/print/bl.odt'));
    $response->headers->set('Content-Type', 'application/vnd.oasis.opendocument.text');
    } else {
    $response = new Response(file_get_contents($this->get('kernel')->getRootDir() . '/../src/Papyrillio/BeehiveBundle/Resources/print/content.xml'));
    $response->headers->set('Content-Type', 'text/xml');
    }

    return $response;
  }

  protected static function getTableHeader($edition){
    if(!in_array($edition->getSort(), array(self::ALLGEMEINES, self::ALEX, self::TAIT))){
      if($edition->getSort() >= self::LOND && $edition->getSort() < self::NACH_LOND){
        return '<table:table-row table:style-name="Lond.1">
                  <table:table-cell table:style-name="Lond.A1" office:value-type="string">
                      <text:p text:style-name="blTableContentPage"/>
                  </table:table-cell>
                  <table:table-cell table:style-name="Lond.A1" office:value-type="string">
                      <text:p text:style-name="blTableContentNumber"/>
                  </table:table-cell>
                  <table:table-cell table:style-name="Lond.A1" office:value-type="string">
                      <text:p text:style-name="blTableContentLine"/>
                  </table:table-cell>
                  <table:table-cell table:style-name="Lond.A1" office:value-type="string">
                      <text:h text:style-name="Heading_20_1" text:outline-level="1">' . $edition->getTitle() . '</text:h>
                  </table:table-cell>
              </table:table-row>';
      } else {
        return '<table:table-row table:style-name="NachAllgemeines.1">
                  <table:table-cell table:style-name="NachAllgemeines.A1" office:value-type="string">
                      <text:p text:style-name="blTableContentNumber"/>
                  </table:table-cell>
                  <table:table-cell table:style-name="NachAllgemeines.A1" office:value-type="string">
                      <text:p text:style-name="blTableContentLine"/>
                  </table:table-cell>
                  <table:table-cell table:style-name="NachAllgemeines.A1" office:value-type="string">
                      <text:h text:style-name="Heading_20_1" text:outline-level="1">' . $edition->getTitle() . '</text:h>
                  </table:table-cell>
              </table:table-row>';
      }
    }
    return '';
  }

  protected static function getTableContent($correction){
    if($correction->getEdition()->getSort() === self::ALLGEMEINES){
      return '<table:table-row table:style-name="Allgemeines.1">
                    <table:table-cell table:style-name="Allgemeines.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Allgemeines.B2" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if($correction->getEdition()->getSort() > self::ALLGEMEINES && $correction->getEdition()->getSort() < self::ALEX) {
      return '<table:table-row table:style-name="NachAllgemeines.1">
                    <table:table-cell table:style-name="NachAllgemeines.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $correction->getText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachAllgemeines.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachAllgemeines.C3" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if($correction->getEdition()->getSort() === self::ALEX) {
      return '<table:table-row table:style-name="Alex.1">
                    <table:table-cell table:style-name="Alex.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentPage">' . ($correction->getPage() ? 'S. ' . $correction->getPage() : '') . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Alex.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber"><text:span text:style-name="T3">' . ($correction->getInventoryNumber() ? 'Inv. ' : '') . '</text:span>' . ($correction->getInventoryNumber() ? $correction->getInventoryNumber() : $correction->getText()) . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Alex.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Alex.D3" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if($correction->getEdition()->getSort() > self::ALEX && $correction->getEdition()->getSort() < self::LOND) {
      return '<table:table-row table:style-name="NachAlex.1">
                    <table:table-cell table:style-name="NachAlex.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $correction->getText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachAlex.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachAlex.C3" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if($correction->getEdition()->getSort() >= self::LOND && $correction->getEdition()->getSort() < self::NACH_LOND) {
      return '<table:table-row table:style-name="Lond.1">
                    <table:table-cell table:style-name="Lond.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentPage">' . ($correction->getPage() ? 'S. ' . $correction->getPage() : '') . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Lond.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $correction->getSortText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Lond.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Lond.D3" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if($correction->getEdition()->getSort() >= self::NACH_LOND && $correction->getEdition()->getSort() < self::TAIT) {
      return '<table:table-row table:style-name="NachLond.1">
                    <table:table-cell table:style-name="NachLond.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $correction->getText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachLond.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachLond.C5" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if($correction->getEdition()->getSort() === self::TAIT) {
      return '<table:table-row table:style-name="Tait.1">
                    <table:table-cell table:style-name="Tait.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentPage">' . ($correction->getPage() ? 'S. ' . $correction->getPage() : '') . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tait.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $correction->getSortText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tait.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Tait.D3" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if($correction->getEdition()->getSort() > self::TAIT) {
      return '<table:table-row table:style-name="NachTait.1">
                    <table:table-cell table:style-name="NachTait.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $correction->getText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachTait.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="NachTait.C3" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(true) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    }
    return '';
  }

  protected function getData($compilationVolume = 13, $editionId){
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
    //$query->setFirstResult(0)->setMaxResults(300); // cl: DEBUG

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
      } else if (isset($correctionsGroupedByEdition[$correction->getEdition()->getId()])){
        $correction->setText('');
      }
      $correctionsGroupedByEdition[$correction->getEdition()->getSort()][] = $correction;
    }
    
    return array('compilation' => $compilation, 'editions' => $correctionsGroupedByEdition);
  }

  public function leidenSnippetAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');

    $corrections = $repository->findBy(array('id' => $id));

    return $this->render('PapyrillioBeehiveBundle:Report:leidenSnippet.html.twig', array('corrections' => $corrections));
  }
}
