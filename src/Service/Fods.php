<?php

namespace App\Service;

use DateTime;
use DOMDocument;
use DOMXPath;

class Fods
{
    const NAMESPACE_TABLE     = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    
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
            $headerLine = 1;
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
      $table = [];
      foreach($xpath->evaluate('//table:table-row[position() > ' . $headerLine . ']') as $row){
        foreach($positions as $fieldName => $fieldIndex){
          $tableRow[$fieldName] = self::getValue($xpath, $row, $fieldIndex);
        }
        $table[] = $tableRow;


          #$id = $this->getValue($row, 'hgv_id_long');
          #if(\preg_match('/^\d+[A-Za-z]*( [XYZ])?$/', $id)){
          #    $hgv = $this->entityManager->getRepository(Hgv::class)->findOneBy(['id' => $id]);
          #    $hgv = $this->generateObjectFromXml($row, $hgv);
          #    echo ($this->flushCounter + 1) . ': ' . $hgv->getPublikationLang() . ' (HGV full ' . $hgv->getId()  . ') [' . $unitOfWorkStates[$this->entityManager->getUnitOfWork()->getEntityState($hgv)] .  ']'  . "\n";
          #}
      }
      return $table;
    }

    protected static function getValue($xpath, $row, $fieldIndex){
      //$fieldIndex = $this->positions[$fieldName];
      $nodeList = $xpath->evaluate('table:table-cell[(count(preceding-sibling::table:table-cell[not(@table:number-columns-repeated)]) + sum(preceding-sibling::table:table-cell/@table:number-columns-repeated) + (1 - count(@table:number-columns-repeated) + sum(@table:number-columns-repeated))) >= ' . $fieldIndex . ']', $row);
      if(!$nodeList->item(0)){
        echo '----------------------------------------------- ' . $fieldName . '/' . $fieldIndex . "\n";
        foreach($xpath->evaluate('table:table-cell', $row) as $node){
          echo trim($node->nodeValue) . " >> ";
          foreach($node->attributes as $att){
            if($att->name == 'number-columns-repeated'){
              echo '[@' . $att->name . '=' . $att->value . ']';
            }
          }
          echo "\n";
        }
        dd($row);
      }
      return trim($nodeList->item(0)->nodeValue);
    }
    
}

?>
