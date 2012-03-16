<?php

namespace Papyrillio\BeehiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable;
use DateTime;
/**
 * Papyrillio\BeehiveBundle\Entity\Correction
 */
class Correction
{
    const STATUS_UNCHECKED = 'unchecked';
    const STATUS_REVIEWED  = 'reviewed';
    const STATUS_FINALISED = 'finalised';
    const STATUS_DEFAULT   = 'finalised';

    protected static $STATUS = array('unchecked', 'reviewed', 'finalised');
    protected static $ROMAN  = array('I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12, 'XIII' => 13, 'XIV' => 14, 'XV' => 15, 'XVI' => 16, 'XVII' => 17, 'XVIII' => 18, 'XIX' => 19, 'XX' => 20, 'XXI' => 21, 'XXII' => 22, 'XXIII' => 23, 'XXIV' => 24, 'XXV' => 25, 'XXVI' => 26, 'XXVII' => 27, 'XXVIII' => 28, 'XXIX' => 29, 'XXX' => 30, 'XXXI' => 31, 'XXXII' => 32, 'XXXIII' => 33, 'XXXIV' => 34, 'XXXV' => 35, 'XXXVI' => 36, 'XXXVII' => 37, 'XXXIX' => 39, 'XXXVIII' => 38, 'XL' => 40, 'XLI' => 41, 'XXIX' => 29, 'XLIII' => 43, 'LIV' => 44, 'XLV' => 45, 'XLVI' => 46, 'XLVII' => 47, 'XLVIII' => 48, 'XLIX' => 49, 'L' => 50, 'LI' => 51, 'LII' => 52, 'LIII' => 53, 'LIV' => 54, 'LV' => 55, 'LVI' => 56, 'LVII' => 57, 'LVIII' => 58, 'LIX' => 59, 'LX' => 60, 'LXI' => 61, 'LXII' => 62, 'LXIII' => 63, 'LXIV' => 64, 'LXV' => 65, 'LXVI' => 66, 'LXVII' => 67, 'LXVIII' => 68, 'LXIX' => 69, 'LXX' => 70, 'LXXI' => 71, 'LXXII' => 72, 'LXXIII' => 73, 'LXXIV' => 74, 'LXXV' => 75, 'LXXVI' => 76, 'LXXVII' => 77, 'LXXVIII' => 78, 'LXXIX' => 79, 'LXXX' => 80, 'LXXXI' => 81, 'LXXXII' => 82, 'LXXXIII' => 83, 'LXXXIV' => 84, 'LXXXV' => 85, 'LXXXVI' => 86, 'LXXXVII' => 87, 'LXXXVIII' => 88, 'LXXXIX' => 89, 'XC' => 90, 'XCI' => 91, 'XCII' => 92, 'XCIII' => 93, 'XCIV' => 94, 'XCV' => 95, 'XCVI' => 96, 'XCVII' => 97, 'XCVIII' => 98, 'XCIX' => 99, 'C' => 100);
    protected static $ALPHA  = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8);
    protected static $SIDE   = array('V°' => 1, 'R°' => 2);

    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->indexEntries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = self::STATUS_UNCHECKED;
        $this->creator = 'system';
        $this->created = new DateTime('now');
    }

    public function getTitle(){
      return $this->getEdition()->getSort() . ' = ' . $this->getEdition()->getTitle();
    }

    public function hasTask($category = null){
      return count($this->getTasks($category));
    }

    public function hasOpenTask($category = null){
      $count = 0;
      foreach($this->getTasks($category) as $task){
        if(!$task->isCleared()){
          $count++;
        }
      }
      return $count;
    }

    public function getTasks($category = null){
      if($category === null){
        return $this->tasks;
      } else {
        $tasks = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->tasks as $task){
          if($task->getCategory() === $category){
            $tasks->add($task);
          }
        }
        return $tasks;
      }
    }

    public function getTaskString($category = null){
      $result = '';
      foreach($this->getTasks($category) as $task){

          $result .= ($category ? '' : $task->getCategory() . ': ') . $task->getDescription() . "; ";

      }
      return rtrim($result, '; ');
    }

    /**
     * Set ddb
     *
     * @param string $ddb
     */
    public function setDdb($ddb)
    {
        $this->ddb = $ddb;

        $tokenList = explode(';', $this->ddb);
        $this->collection = array_key_exists(0, $tokenList) ? $tokenList[0] : '';
        $this->volume     = array_key_exists(1, $tokenList) ? $tokenList[1] : '';
        $this->document   = array_key_exists(2, $tokenList) ? $tokenList[2] : '';
    }

    /**
     * Set tm
     *
     * @param integer $tm
     */
    public function setTm($tm)
    {
        $this->tm = $tm;
        $this->folder = ceil($this->tm / 1000.0);
    }

    /**
     * Set hgv
     *
     * @param string $hgv
     */
    public function setHgv($hgv)
    {
        $this->hgv = $hgv;
        $this->setTm(preg_replace('/[^\d]+/', '', $hgv) * 1);
    }

    public function getLink($type = 'pi'){
      switch($type){
        case 'pi':
          return $this->ddb ? 'http://www.papyri.info/ddbdp/' . $this->ddb : null;
        case 'biblio':
          return $this->source ? 'http://www.papyri.info/biblio/' . $this->source : null;
        case 'githubddb':
          return $this->collection ? 'https://github.com/papyri/idp.data/blob/master/DDB_EpiDoc_XML/'. $this->collection . '/'. $this->collection . '.'. $this->volume . '/'. $this->collection . '.'. $this->volume . '.' . $this->document . '.xml' : null;
        case 'githubhgv':
          return $this->hgv && $this->folder ? 'https://github.com/papyri/idp.data/blob/master/HGV_meta_EpiDoc/HGV' . $this->folder . '/' . $this->hgv . '.xml' : null;
        case 'hgv':
          return $this->collection && $this->volume && $this->document ? 'http://www.papy.uni-heidelberg.de/Hauptregister/FMPro?-DB=Hauptregister_&-Format=DTableVw.htm&Publikation='. $this->collection . '&Band='. $this->volume . '&Nummer='. $this->document . '&-Max=20&-Find' : null;
        default:
          return null;
      }
    }

    /**
     * Set status
     *
     * @param text $status
     */
    public function setStatus($status)
    {
        if(in_array($status, self::$STATUS)){
          $this->status = $status;
        } else {
          $this->status = self::STATUS_DEFAULT;
        }
    }

    /**
     * Set position
     * 
     * fragment := A, B, C, D, E, F, G, H (up to 8 fragements)
     * column := I, II, III, IV, V, VI, VII, ... LXXXIX (up to 89 columns)
     * line := 1, 2, 3, 4, 5, ... ∞
     * 
     * @param text $position
     */
    public function setPosition($position)
    {
        // retrieve fragment, column and line from position string

        $this->sortSide = $this->sortFragment = $this->sortColumn = $this->sortLine = null;
        if(preg_match('/([RV]°)( |,|-|$)/', $position, $matches)){
          $this->sortSide = $matches[1];
        }
        if(preg_match('/([ABCDEFGH])( |,|-|$)/', $position, $matches)){
          $this->sortFragment = $matches[1];
        }
        if(preg_match('/([IVXL]+)( |,|-|$)/', $position, $matches)){
          $this->sortColumn = $matches[1];
        }
        if(preg_match('/(\d+)( |,|-|$)/', $position, $matches)){
          $this->sortLine = $matches[1];
        }

        // calculate system sort
        $this->sortSystem = $this->generateSytemSort($this->sortSide, $this->sortFragment, $this->sortColumn, $this->sortLine);

        // define final sort parameter
        if($this->sortUser !== null){
          $this->sort = $this->sortUser;
        } else {
          $this->sort = $this->sortSystem;
        }
        $this->position = $position;
    }

     /**
     * Set generateSytemSort
     * 
     * fragment := A, B, C, E, F, G, H (up to 7 fragements)
     * column := I, II, III, IV, V, VI, VII, ... (up to 99 columns)
     * line := 1, 2, 3, 4, 5, ...
     * 
     * @param $fragment, i.e. A, B, C, E, F, G or H
     * @param $column, i.e. I, II, III, IV, V, VI, ...
     * @param $line, i.e. 1, 2, 3, 4, 5, 6, ...
     */
    protected function generateSytemSort($side, $fragment, $column, $line){
      $s = $side != null ? self::$SIDE[$side] * 1000000 : 0;
      $f = $fragment != null ? self::$ALPHA[$fragment] * 100000 : 0;
      $c = $column != null ? self::$ROMAN[$column] * 1000 : 0;
      $l = $line != null ? $line : 0;
      
      return $s + $f + $c + $l;
    }

    public function getLinks(){
      $links = array();
      foreach(array('pi' => 'papyri.info', 'githubddb' => 'github DDB', 'githubhgv' => 'github HGV', 'hgv' => 'HGV', 'biblio' => 'Biblio') as $type => $name){
        if($this->getLink($type)){
          $links[$name] = $this->getLink($type);
        }
      }
      return $links;
    }

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $source
     */
    private $source;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var integer $tm
     */
    private $tm;

    /**
     * @var integer $folder
     */
    private $folder;

    /**
     * @var string $hgv
     */
    private $hgv;

    /**
     * @var string $ddb
     */
    private $ddb;
    /**
     * @var string $collection
     */
    private $collection;

    /**
     * @var string $volume
     */
    private $volume;

    /**
     * @var string $document
     */
    private $document;

    /**
     * @var text $position
     */
    private $position;

    /**
     * @var text $description
     */
    private $description;

    /**
     * @var text $status
     */
    private $status;

    /**
     * @var text $creator
     */
    private $creator;

    /**
     * @var datetime $created
     */
    private $created;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Task
     */
    private $tasks;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Compilation
     */
    private $compilation;
   
    /**
     * @var Papyrillio\BeehiveBundle\Entity\Edition
     */
    private $edition;

    /**
     * @var string $sortSide
     */
    private $sortSide;

    /**
     * @var string $sortFragment
     */
    private $sortFragment;

    /**
     * @var string $sortColumn
     */
    private $sortColumn;

    /**
     * @var integer $sortLine
     */
    private $sortLine;

    /**
     * @var integer $sortSystem
     */
    private $sortSystem;

    /**
     * @var integer $sortUser
     */
    private $sortUser;

    /**
     * @var integer $sort
     */
    private $sort;
    
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
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Set source
     *
     * @param integer $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Get source
     *
     * @return integer 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get tm
     *
     * @return integer 
     */
    public function getTm()
    {
        return $this->tm;
    }

    /**
     * Set folder
     *
     * @param integer $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * Get folder
     *
     * @return integer 
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Get hgv
     *
     * @return string 
     */
    public function getHgv()
    {
        return $this->hgv;
    }

    /**
     * Get ddb
     *
     * @return string 
     */
    public function getDdb()
    {
        return $this->ddb;
    }

    /**
     * Set collection
     *
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get collection
     *
     * @return string 
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set volume
     *
     * @param string $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * Get volume
     *
     * @return string 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set document
     *
     * @param string $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * Get document
     *
     * @return string 
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Get position
     *
     * @return text 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get status
     *
     * @return text 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set creator
     *
     * @param text $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Get creator
     *
     * @return text 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set sortSide
     *
     * @param string $sortSide
     */
    public function setSortSide($sortSide)
    {
        $this->sortSide = $sortSide;
    }

    /**
     * Get sortSide
     *
     * @return string 
     */
    public function getSortSide()
    {
        return $this->sortSide;
    }

    /**
     * Set sortFragment
     *
     * @param string $sortFragment
     */
    public function setSortFragment($sortFragment)
    {
        $this->sortFragment = $sortFragment;
    }

    /**
     * Get sortFragment
     *
     * @return string 
     */
    public function getSortFragment()
    {
        return $this->sortFragment;
    }

    /**
     * Set sortColumn
     *
     * @param string $sortColumn
     */
    public function setSortColumn($sortColumn)
    {
        $this->sortColumn = $sortColumn;
    }

    /**
     * Get sortColumn
     *
     * @return string 
     */
    public function getSortColumn()
    {
        return $this->sortColumn;
    }

    /**
     * Set sortLine
     *
     * @param integer $sortLine
     */
    public function setSortLine($sortLine)
    {
        $this->sortLine = $sortLine;
    }

    /**
     * Get sortLine
     *
     * @return integer 
     */
    public function getSortLine()
    {
        return $this->sortLine;
    }

    /**
     * Set sortSystem
     *
     * @param integer $sortSystem
     */
    public function setSortSystem($sortSystem)
    {
        $this->sortSystem = $sortSystem;
    }

    /**
     * Get sortSystem
     *
     * @return integer 
     */
    public function getSortSystem()
    {
        return $this->sortSystem;
    }

    /**
     * Set sortUser
     *
     * @param integer $sortUser
     */
    public function setSortUser($sortUser)
    {
        $this->sortUser = $sortUser;
    }

    /**
     * Get sortUser
     *
     * @return integer 
     */
    public function getSortUser()
    {
        return $this->sortUser;
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
     * Add tasks
     *
     * @param Papyrillio\BeehiveBundle\Entity\Task $tasks
     */
    public function addTask(\Papyrillio\BeehiveBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;
    }

    /**
     * Set compilation
     *
     * @param Papyrillio\BeehiveBundle\Entity\Compilation $compilation
     */
    public function setCompilation(\Papyrillio\BeehiveBundle\Entity\Compilation $compilation)
    {
        $this->compilation = $compilation;
    }

    /**
     * Get compilation
     *
     * @return Papyrillio\BeehiveBundle\Entity\Compilation 
     */
    public function getCompilation()
    {
        return $this->compilation;
    }


    /**
     * Set edition
     *
     * @param Papyrillio\BeehiveBundle\Entity\Edition $edition
     */
    public function setEdition(\Papyrillio\BeehiveBundle\Entity\Edition $edition)
    {
        $this->edition = $edition;
    }

    /**
     * Get edition
     *
     * @return Papyrillio\BeehiveBundle\Entity\Edition 
     */
    public function getEdition()
    {
        return $this->edition;
    }
    /**
     * @var Papyrillio\BeehiveBundle\Entity\IndexEntry
     */
    private $indexEntries;


    /**
     * Add indexEntries
     *
     * @param Papyrillio\BeehiveBundle\Entity\IndexEntry $indexEntries
     */
    public function addIndexEntry(\Papyrillio\BeehiveBundle\Entity\IndexEntry $indexEntries)
    {
        $this->indexEntries[] = $indexEntries;
    }

    /**
     * Get indexEntries
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getIndexEntries()
    {
        return $this->indexEntries;
    }
}