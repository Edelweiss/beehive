<?php

namespace Stof\DoctrineExtensionsBundle\Entity;

/**
 * All required columns are mapped through inherited superclass
 */
class LogEntry extends AbstractLogEntry
{
  public function getDataDecoded() // cl
  {
      $dataDecoded = array();
      foreach($this->data as $key => $value){
        if($value instanceof DateTime){
          $dataDecoded[$key] = $value->format('Y-m-d H:i:s');
        } else {
          $dataDecoded[$key] = \Papyrillio\BeehiveBundle\Entity\Correction::decode4Byte($value);
        }
      }
      return $dataDecoded;
  }
}