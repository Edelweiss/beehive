<?php

namespace App\Entity;

use App\Repository\EditionRepository;
use Doctrine\ORM\Mapping as ORM;

class Edition
{
    const MATERIAL_PAPYRUS = 'Papyrus';
    const MATERIAL_OSTRACON = 'Ostrakon';
    const MATERIAL_DEFAULT = 'Papyrus';
    
    protected static $MATERIAL = array('Papyrus', 'Ostrakon');

    public function setMaterial($material)
    {
        if(in_array($material, self::$MATERIAL)){
          $this->material = $material;
        } else {
          $this->material = self::MATERIAL_DEFAULT;
        }
    }

    public function __construct()
    {
        $this->corrections = new \Doctrine\Common\Collections\ArrayCollection();
        $this->docketEntries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->material = self::MATERIAL_DEFAULT;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        $this->remark = $this->volume = $this->collection = '';
        $matches = array();
        if(preg_match('/\((.+)\)/', $title, $matches)){
          $this->remark = $matches[1];
        }
        if(preg_match('/ (\d+[ABCDEFGHIJKLMNOPQRSTUVWXYZ]?|\d+ \d+|\d+\. \d+|\d+ Gr\. \d+)($| )/', $title, $matches)){
          $this->volume = $matches[1];
        }
        if(preg_match('/^([^\(\d]+)/', $title, $matches)){
          $this->collection = rtrim($matches[1], ' ');
        }
    }

    public function getPoStrippedTitle(){
      $matches = array();
      if(preg_match('/^[PO]\. (.+)$/', $this->title, $matches)){
        return $matches[1] . ' [' . $matches[0] . ']';
      }
      return $this->title;
    }

    public function getCodeTitle(){
      $arabs = array_flip(Correction::$ROMAN);
      $code = $this->title;

      mb_ereg_search_init($code, '[0-9]+');
      $search = mb_ereg_search();

      if($search){
        $result = mb_ereg_search_getregs();
        do {
          $code = mb_ereg_replace($result[0], $arabs[$result[0]], $code);
        } while($result = mb_ereg_search_regs());
      }

      $code = mb_ereg_replace('[^a-zA-z]', '', $code);
      return $code;
    }

    public function __toString(){
      return implode(';', array($this->id, $this->sort, $this->title, $this->collection, $this->volume, $this->remark, $this->material));
    }

    private $id;
    private $sort;
    private $title;
    private $collection;
    private $volume;
    private $remark;
    private $material;
    private $corrections;
    private $docketEntries;

    public function getId()
    {
        return $this->id;
    }
    public function setSort($sort)
    {
        $this->sort = $sort;
    }
    public function getSort()
    {
        return $this->sort;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }
    public function getCollection()
    {
        return $this->collection;
    }
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }
    public function getVolume()
    {
        return $this->volume;
    }
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }
    public function getRemark()
    {
        return $this->remark;
    }
    public function getMaterial()
    {
        return $this->material;
    }
    public function addCorrection(\App\Entity\Correction $corrections)
    {
        $this->corrections[] = $corrections;
    }

    /**
     * Get corrections
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCorrections()
    {
        return $this->corrections;
    }
    public function addDocketEntry(\App\Entity\Docket $docketEntry)
    {
        $this->docketEntries[] = $docketEntry;
    }
    public function getDocketEntries()
    {
        return $this->docketEntries;
    }
}