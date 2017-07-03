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
  const LOND1            = 580000;
  const LOND2            = 581000;
  const LOND3            = 582000;
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

  public function overviewAction($compilationVolume = 13){
    return $this->render('PapyrillioBeehiveBundle:Report:overview.html.twig', $this->getData($compilationVolume));
  }

  public function printAction($compilationVolume = 13, $editionId){
    return $this->render('PapyrillioBeehiveBundle:Report:print.html.twig', $this->getData($compilationVolume, $editionId));
  }

  public function pdfAction($compilationVolume = 13, $editionId){
    $data = $this->getData($compilationVolume, $editionId);
    $xml = $this->getXml($data);

    $oo = file_get_contents($this->get('kernel')->getRootDir() . '/../src/Papyrillio/BeehiveBundle/Resources/print/content_null.xml');
    foreach($xml as $key => $xmlSnippet){
      $oo = str_replace('<!--' . $key . '-->', $xmlSnippet, $oo);
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

  protected function getXml(Array $data){
    $xml = array();

    foreach($data['editions'] as $editionSort => $corrections){
      $edition = $corrections[0]->getEdition();
      $xml[$edition->getCodeTitle()] = '';

      foreach($corrections as $correction){
       $xml[$edition->getCodeTitle()] .= self::getTableRow($correction);
      }
    }
    return $xml;
  }

  protected function getXml2(Array $data){
    $xmlStyle = '';
    $xmlTables = '';

    foreach($data['editions'] as $editionSort => $corrections){
      //echo 'editionSort: '. $editionSort . ' - ' . $xmlPointer . "\n";

      $edition = $corrections[0]->getEdition();
      $xmlStyle .= self::getStyle($edition);
      $xmlTables .= self::getHeader($edition);
      $xmlTables .= self::getTableStart($edition);
      foreach($corrections as $correction){
        $xmlTables .= self::getTableRow($correction);
      }
      $xmlTables .= self::getTableEnd($edition);
    }
    return array('BL' => $xmlTables, 'BLSTYLE' => $xmlStyle);
  }

  protected function getXml1(Array $data){
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
    return $xml;
  }


  protected static function getStyle($edition){
    $code = $edition->getCodeTitle();
    if($edition->getSort() === self::ALLGEMEINES){
      return '';
    } else if(in_array($edition->getSort(), array(self::ALEX, self::LOND1, self::LOND2, self::LOND3, self::TAIT))){
      return '<style:style style:name="' . $code . '" style:family="table">
            <style:table-properties style:width="11.7cm" table:align="margins"/>
        </style:style>
        <style:style style:name="' . $code . '.A" style:family="table-column">
            <style:table-column-properties style:column-width="1.328cm" style:rel-column-width="7439*"/>
        </style:style>
        <style:style style:name="' . $code . '.B" style:family="table-column">
            <style:table-column-properties style:column-width="1.782cm" style:rel-column-width="9978*"/>
        </style:style>
        <style:style style:name="' . $code . '.C" style:family="table-column">
            <style:table-column-properties style:column-width="0.921cm" style:rel-column-width="5157*"/>
        </style:style>
        <style:style style:name="' . $code . '.D" style:family="table-column">
            <style:table-column-properties style:column-width="7.669cm" style:rel-column-width="42961*"/>
        </style:style>
        <style:style style:name="' . $code . '.1" style:family="table-row">
            <style:table-row-properties fo:keep-together="always"/>
        </style:style>
        <style:style style:name="' . $code . '.A1" style:family="table-cell">
            <style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0.097cm" fo:padding-bottom="0.097cm" fo:border-left="none" fo:border-right="0.05pt solid #000000" fo:border-top="none" fo:border-bottom="none"/>
        </style:style>
        <style:style style:name="' . $code . '.B1" style:family="table-cell">
            <style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="none"/>
        </style:style>
        <style:style style:name="' . $code . '.D1" style:family="table-cell">
            <style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
        </style:style>';
    } else {
      return '<style:style style:name="' . $code . '" style:family="table">
            <style:table-properties style:width="11.7cm" table:align="margins"/>
        </style:style>
        <style:style style:name="' . $code . '.A" style:family="table-column">
            <style:table-column-properties style:column-width="1.499cm" style:rel-column-width="8398*"/>
        </style:style>
        <style:style style:name="' . $code . '.B" style:family="table-column">
            <style:table-column-properties style:column-width="1.55cm" style:rel-column-width="8684*"/>
        </style:style>
        <style:style style:name="' . $code . '.C" style:family="table-column">
            <style:table-column-properties style:column-width="8.65cm" style:rel-column-width="48453*"/>
        </style:style>
        <style:style style:name="' . $code . '.1" style:family="table-row">
            <style:table-row-properties fo:keep-together="always"/>
        </style:style>
        <style:style style:name="' . $code . '.A1" style:family="table-cell">
            <style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border="none"/>
        </style:style>
        <style:style style:name="' . $code . '.C1" style:family="table-cell">
            <style:table-cell-properties fo:padding-left="0.101cm" fo:padding-right="0.101cm" fo:padding-top="0cm" fo:padding-bottom="0cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
        </style:style>';
    }
  }

  protected static function getHeader($edition){
    if($edition->getSort() === self::ALLGEMEINES){
        return '';
    }
    return '<text:h text:style-name="Heading_20_1" text:outline-level="1">' . $edition->getTitle() . '</text:h>';
  }

  protected static function getTableStart($edition){
    if($edition->getSort() === self::ALLGEMEINES){
      return '<table:table table:name="Allgemeines" table:style-name="Allgemeines">
                <table:table-column table:style-name="Allgemeines.A"/>
                <table:table-column table:style-name="Allgemeines.B"/>';
    } else if(in_array($edition->getSort(), array(self::ALEX, self::LOND1, self::LOND2, self::LOND3, self::TAIT))){
      return '<table:table table:name="' . $edition->getCodeTitle() .  '" table:style-name="' . $edition->getCodeTitle() .  '">
                <table:table-column table:style-name="' . $edition->getCodeTitle() .  '.A"/>
                <table:table-column table:style-name="' . $edition->getCodeTitle() .  '.B"/>
                <table:table-column table:style-name="' . $edition->getCodeTitle() .  '.C"/>
                <table:table-column table:style-name="' . $edition->getCodeTitle() .  '.D"/>';
    } else {
      return '<table:table table:name="' . $edition->getCodeTitle() .  '" table:style-name="' . $edition->getCodeTitle() .  '">
                <table:table-column table:style-name="' . $edition->getCodeTitle() .  '.A"/>
                <table:table-column table:style-name="' . $edition->getCodeTitle() .  '.B"/>
                <table:table-column table:style-name="' . $edition->getCodeTitle() .  '.C"/>';
    }
  }
  protected static function getTableEnd($edition){
    return '</table:table>';
  }

  protected static function getTableRow($correction){
    $code = $correction->getEdition()->getCodeTitle();
    if($correction->getEdition()->getSort() === self::ALLGEMEINES){
      return '<table:table-row table:style-name="Allgemeines.1">
                    <table:table-cell table:style-name="Allgemeines.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="Allgemeines.B1" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else if(in_array($correction->getEdition()->getSort(), array(self::ALEX, self::LOND1, self::LOND2, self::LOND3, self::TAIT))){
      $blTableContentNumber = $blTableContentNumber = $correction->getSortText();
      if($correction->getEdition()->getSort() === self::ALEX) {
        $blTableContentNumber = '<text:span text:style-name="T3">' . ($correction->getInventoryNumber() ? 'Inv. ' : '') . '</text:span>' . ($correction->getInventoryNumber() ? $correction->getInventoryNumber() : $correction->getText());
      }
      return '<table:table-row table:style-name="' . $code . '.1">
                    <table:table-cell table:style-name="' . $code . '.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentPage">' . ($correction->getPage() ? 'S. ' . $correction->getPage() : '') . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="' . $code . '.B1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $blTableContentNumber . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="' . $code . '.B1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="' . $code . '.D1" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    } else {
      return '<table:table-row table:style-name="' . $code . '.1">
                    <table:table-cell table:style-name="' . $code . '.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentNumber">' . $correction->getText() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="' . $code . '.A1" office:value-type="string">
                        <text:p text:style-name="blTableContentLine">' . $correction->getPosition() . '</text:p>
                    </table:table-cell>
                    <table:table-cell table:style-name="' . $code . '.C1" office:value-type="string">
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    }
 
    return '';
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
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
                        <text:p text:style-name="blTableContentCorrection">' . $correction->getDescription(Correction::MODE_OOXML) . '</text:p>
                    </table:table-cell>
                </table:table-row>';
    }
    return '';
  }

  protected function getData($compilationVolume = 13, $editionId = null){
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
    //$query->setFirstResult(0)->setMaxResults(100); // cl: DEBUG

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
