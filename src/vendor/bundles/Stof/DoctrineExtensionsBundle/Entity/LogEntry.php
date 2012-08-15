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
        if (is_string($value)) {
          $dataDecoded[$key] = \Papyrillio\BeehiveBundle\Entity\Correction::decode4Byte($value);
        } else if($value instanceof DateTime){
          $dataDecoded[$key] = $value->format('Y-m-d H:i:s');
        } else if ($value === null) {
          $dataDecoded[$key] = null;
        }
      }
      return $dataDecoded;
  }
}