<?php

namespace App\Entity;

use App\Repository\EditionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Papyrillio\BeehiveBundle\Entity\Edition
 */
class Edition
{
    const MATERIAL_PAPYRUS = 'Papyrus';
    const MATERIAL_OSTRACON = 'Ostrakon';
    const MATERIAL_DEFAULT = 'Papyrus';
    
    protected static $MATERIAL = array('Papyrus', 'Ostrakon');

    /**
     * Set material
     *
     * @param text $material
     */
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
        $this->material = self::MATERIAL_DEFAULT;
    }

    /**
     * Set title
     *
     * @param text $title
     */
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

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $key
     */
    private $sort;

    /**
     * @var text $title
     */
    private $title;
    /**
     * @var text $collection
     */
    private $collection;

    /**
     * @var integer $volume
     */
    private $volume;

    /**
     * @var text $remark
     */
    private $remark;

    /**
     * @var text $material
     */
    private $material;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Correction
     */
    private $corrections;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sort
     *
     * @param integer $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * Get sort
     *
     * @return integer 
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Get title
     *
     * @return text 
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set collection
     *
     * @param text $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get collection
     *
     * @return text 
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set volume
     *
     * @param integer $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * Get volume
     *
     * @return integer 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set remark
     *
     * @param text $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * Get remark
     *
     * @return text 
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * Get material
     *
     * @return text 
     */
    public function getMaterial()
    {
        return $this->material;
    }
    /**
     * Add corrections
     *
     * @param Papyrillio\BeehiveBundle\Entity\Correction $corrections
     */
    public function addCorrection(\Papyrillio\BeehiveBundle\Entity\Correction $corrections)
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
}