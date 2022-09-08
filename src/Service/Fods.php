<?php

namespace App\Service;

use DateTime;
use DOMDocument;
use DOMXPath;

class Fods
{
    const NAMESPACE_TABLE     = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    const IMPORT_DIR          = __DIR__ . '/../../data';

    function __construct(){

    }
    
    public static function getArray($importFile, $tableName, $headerLine, $headerKey, $fieldNames){
      // parameters
      $headerLine += 0;

      // xpath
      $doc = new DOMDocument();
      $doc->load($importFile);
      $xpath = new DOMXPath($doc);
      $xpath->registerNamespace('table', self::NAMESPACE_TABLE);

      // column positions
      $positions = [];
      if($headerLine <= 0){
          if(!empty($headerKey)){
            $headerLine = count($xpath->evaluate("//table:table-cell[normalize-space(.) = '" . $headerKey . "']/ancestor::table:table-row/preceding-sibling::table:table-row")) + 1;
          } else {
            $headerLine = 1;;
          }
      }
      foreach($xpath->evaluate("//table:table[@table:name='" . $tableName . "']//table:table-row[" . $headerLine . "]//table:table-cell") as $position => $index){
        $name = trim($index->nodeValue);
        $column =  intval($position + 1 + $xpath->evaluate('sum(./preceding-sibling::table:table-cell/@table:number-columns-repeated) - count(preceding-sibling::table:table-cell[number(@table:number-columns-repeated) > 1])', $index));
        if(in_array($name, $fieldNames)){
          $positions[$name] = $column;
        }
      }
      foreach($positions as $key => $position){
        echo $key . ' > ' . $position . "\n";
      }

      // data rows
      foreach($xpath->evaluate('//table:table-row[position() > ' . $headerLine . ']') as $row){
          #$id = $this->getValue($row, 'hgv_id_long');
          #if(\preg_match('/^\d+[A-Za-z]*( [XYZ])?$/', $id)){
          #    $hgv = $this->entityManager->getRepository(Hgv::class)->findOneBy(['id' => $id]);
          #    $hgv = $this->generateObjectFromXml($row, $hgv);
          #    echo ($this->flushCounter + 1) . ': ' . $hgv->getPublikationLang() . ' (HGV full ' . $hgv->getId()  . ') [' . $unitOfWorkStates[$this->entityManager->getUnitOfWork()->getEntityState($hgv)] .  ']'  . "\n";
          #}
      }

    }
    
}

?>
