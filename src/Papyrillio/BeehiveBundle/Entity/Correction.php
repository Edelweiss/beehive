<?php

namespace Papyrillio\BeehiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable;
use Doctrine\ORM\Event\LifecycleEventArgs; // prePersist
use Doctrine\ORM\Event\OnFlushEventArgs; // onFlush
use Doctrine\ORM\Event\PreUpdateEventArgs; // preUpdate
use DateTime;
use Exception;
/**
 * Papyrillio\BeehiveBundle\Entity\Correction
 */
class Correction
{
    const STATUS_UNCHECKED = 'unchecked';
    const STATUS_REVIEWED  = 'reviewed';
    const STATUS_FINALISED = 'finalised';
    const STATUS_DEFAULT   = 'finalised';
    
    const ALLGEMEINES      = 0;
    const NACH_ALLGEMEINES = 1;
    const ALEX             = 25000;
    const NACH_ALEX        = 25001;
    const LOND             = 580000;
    const NACH_LOND        = 582001;
    const TAIT             = 1250000;
    const NACH_TAIT        = 1250001;

    protected static $STATUS = array('unchecked', 'reviewed', 'finalised');
    protected static $ROMAN  = array('I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12, 'XIII' => 13, 'XIV' => 14, 'XV' => 15, 'XVI' => 16, 'XVII' => 17, 'XVIII' => 18, 'XIX' => 19, 'XX' => 20, 'XXI' => 21, 'XXII' => 22, 'XXIII' => 23, 'XXIV' => 24, 'XXV' => 25, 'XXVI' => 26, 'XXVII' => 27, 'XXVIII' => 28, 'XXIX' => 29, 'XXX' => 30, 'XXXI' => 31, 'XXXII' => 32, 'XXXIII' => 33, 'XXXIV' => 34, 'XXXV' => 35, 'XXXVI' => 36, 'XXXVII' => 37, 'XXXIX' => 39, 'XXXVIII' => 38, 'XL' => 40, 'XLI' => 41, 'XXIX' => 29, 'XLIII' => 43, 'LIV' => 44, 'XLV' => 45, 'XLVI' => 46, 'XLVII' => 47, 'XLVIII' => 48, 'XLIX' => 49, 'L' => 50, 'LI' => 51, 'LII' => 52, 'LIII' => 53, 'LIV' => 54, 'LV' => 55, 'LVI' => 56, 'LVII' => 57, 'LVIII' => 58, 'LIX' => 59, 'LX' => 60, 'LXI' => 61, 'LXII' => 62, 'LXIII' => 63, 'LXIV' => 64, 'LXV' => 65, 'LXVI' => 66, 'LXVII' => 67, 'LXVIII' => 68, 'LXIX' => 69, 'LXX' => 70, 'LXXI' => 71, 'LXXII' => 72, 'LXXIII' => 73, 'LXXIV' => 74, 'LXXV' => 75, 'LXXVI' => 76, 'LXXVII' => 77, 'LXXVIII' => 78, 'LXXIX' => 79, 'LXXX' => 80, 'LXXXI' => 81, 'LXXXII' => 82, 'LXXXIII' => 83, 'LXXXIV' => 84, 'LXXXV' => 85, 'LXXXVI' => 86, 'LXXXVII' => 87, 'LXXXVIII' => 88, 'LXXXIX' => 89, 'XC' => 90, 'XCI' => 91, 'XCII' => 92, 'XCIII' => 93, 'XCIV' => 94, 'XCV' => 95, 'XCVI' => 96, 'XCVII' => 97, 'XCVIII' => 98, 'XCIX' => 99, 'C' => 100);
    protected static $ALPHA  = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8);

    public static $ENCODE = array(
      '𐅵' => 'f09085b5', // one half
      '𐅷' => 'f09085b7', // two thirds
      '𐅸' => 'f09085b8' // three quarters
    );
  
    public static $DECODE = array(
      'f09085b5' => '𐅵',
      'f09085b7' => '𐅷',
      'f09085b8' => '𐅸'
    );
    
    public static function encode4Byte($string){
      mb_internal_encoding('UTF-8');
      mb_regex_encoding('UTF-8');
      foreach(self::$ENCODE as $character => $code){
        if(mb_strpos($string, $character) !== FALSE){
          $string = mb_ereg_replace($character, $code, $string);
        }
      }
      return $string;
    }
    
    public static function decode4Byte($string){
      mb_internal_encoding('UTF-8');
      mb_regex_encoding('UTF-8');
      foreach(self::$ENCODE as $character => $code){
        if(mb_strpos($string, $code) !== FALSE){
          $string = mb_ereg_replace($code, $character, $string);
        }
      }
      return $string;
    }

    public function encode(){
      $this->description = self::encode4Byte($this->description);
    }
  
    public function decode(){
      $this->description = self::decode4Byte($this->description);
    }

    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->indexEntries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = self::STATUS_UNCHECKED;
        $this->creator = 'system';
        $this->sort = $this->sortSystem = '';
        $this->created = new DateTime('now');
    }
    
    public function getPage(){ // CROMULENT: should be in database as sortPage
			if(preg_match('/^([^()]*)\((S\. +)?([\dIVXLCDM]+)(-\d+)?\)(,? \([^)]+\))*([^()]*)$/', $this->text, $matches)){
				return $matches[3];
			}
			return '';
    }
    
    public function getInventoryNumber(){ // CROMULENT?! see above
      if(preg_match('/^Inv\. (\d+)([^\d].*)?$/', $this->text, $matches)){
        return $matches[1];
      }
      return '';
    }
    
    public function getSortText(){ // CROMULENT: should be in database
			if(preg_match('/^([^()]*)\((S\. +)?([\dIVXLCDM]+)(-\d+)?\)(,? \([^)]+\))*([^()]*)$/', $this->text, $matches)){
				return trim($matches[1]) . trim($matches[6]);
			}
			return '';
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
     * Set text
     *
     * @param string $text
     */
    public function setText($text){
      $this->text = $text;
      $this->setSortValues();
    }

    /**
     * Set position
     * 
     * @param text $position
     */
    public function setPosition($position){
      $this->position = $position;
      $this->setSortValues();
    }

    /**
     * Set edition
     *
     * @param Papyrillio\BeehiveBundle\Entity\Edition $edition
     */
    public function setEdition(\Papyrillio\BeehiveBundle\Entity\Edition $edition){
        $this->edition = $edition;
        $this->setSortValues();
    }

    public function setSortValues(){
      if($this->getEdition()){

        // retrieve page, fragment, column and line from position string
        $this->sortPage = $this->sortSide = $this->sortFragment = $this->sortColumn = $this->sortLine = null;
        $sortText = $this->text;

        if($this->getEdition()->getSort() === self::ALLGEMEINES){
          $sortText = mb_substr($sortText, 0, 8);
          while(mb_strlen($sortText) < 8){
            $sortText .= '.';
          }
        }

        echo '<pre>';
        var_dump($sortText);
        echo '</pre>';
        if(preg_match('/^(\d+)([\-+ .,]*(\d+))*(( | Fr. )?\(?([*a-zA-Z])((-|\+| |, ?)[*a-zA-Z])*\)?)?( \((\d+)(-\d+)?\)(, \(\d+\))*)?( ([RV]°))?( \(S\. (\d+)\))?( konkave Innenseite)?$/', $sortText, $matches)){
        //               1    2         3      45            6          78                           9  10    11       12            13 14      15       16
        echo '<pre>';
        var_dump($matches);
        echo '</pre>';
          $sortText = $matches[1];
          if(count($matches) > 16){
            $this->sortPage = $matches[16];
            $this->sortSide = $matches[14];
            $this->sortFragment = $matches[6];
          } else if(count($matches) > 14){
            $this->sortSide = $matches[14];
            $this->sortFragment = $matches[6];
          } else if(count($matches) > 10){
            $this->sortColumn = $matches[10];
            $this->sortFragment = $matches[6];
          }  else if(count($matches) > 6){
            $this->sortFragment = $matches[6];
          } else if(count($matches) > 3){
            $this->sortPage = $matches[3];
          }
        }

        if(preg_match('/^passim$/', $sortText, $matches)){
          $sortText = 0;
        }

        if(preg_match('/^(\d+)(bis)$/', $sortText, $matches)){
          $sortText = $matches[1];
          $this->sortPage = $matches[2];
        }

        // for text ~= (S. 147) 426 ~= 390 (S. 332) ~= 1 (S. XVIII) ~= 9604 (11), (18), (19)
        if(preg_match('/^([^()]*)\((S\. +)?([\dIVXLCDM]+)(-\d+)?\)(,? \([^)]+\))*([^()]*)$/', $sortText, $matches)){
        //               1         2       3             4        5              6
          echo '1 ' . '[' . trim($matches[1]) . '][' . trim($matches[2]) . ']{' . trim($matches[3]) . '}[' . trim($matches[4]) . '][' . trim($matches[5]) . '][' . trim($matches[6]) . ']';
          $this->sortPage = $matches[3];
          $sortText = trim($matches[1]) . trim($matches[6]);
        }

        if(preg_match('/^Inv\. (\d+)?$/', $sortText, $matches)){
          $sortText = 1000000 + $matches[1];
        }

        if(preg_match('/([RV]°)( |,|-|\)|$)/', $this->position, $matches)){
          $this->sortSide = $matches[1];
        }
        if(preg_match('/([ABCDEFGH])( |,|-|\)|$)/', $this->position, $matches)){
          $this->sortFragment = $matches[1];
        } else if(preg_match('/Fr\. (\d+)( |,|-|\)|$)/', $this->position, $matches)){
          $this->sortFragment = $matches[1];
        }
        if(preg_match('/([IVXL]+)( |,|-|\)|$)/', $this->position, $matches)){
          $this->sortColumn = $matches[1];
        }
        if(preg_match('/(\d+)( |,|-|\)|$)/', $this->position, $matches)){
          $this->sortLine = $matches[1];
        }
  
        // calculate system sort
        $this->sortSystem = $this->generateSytemSort($this->getEdition()->getSort(), $sortText, $this->sortPage, $this->sortSide, $this->sortFragment, $this->sortColumn, $this->sortLine);

        // define final sort parameter
        $this->sort = $this->sortUser !== null ? $this->sortUser : $this->sortSystem;
      }
    }

     /**
     * generate code sytem sort parameter
     * sort by edition > text > page > side > fragment > column > line
     * SELECT e.sort, text, position, sortPage, sortSide, sortFragment, sortColumn, sortLine, sortSystem from correction c JOIN edition e ON c.edition_id = e.id ORDER BY sortSystem;
     * 
     * @param $edition 0 (Allgemein) ... 1'300'000 (Tavolette Varie); exceptions for P.Lond P.Petr O.Tait
     * @param $text
     * @param $page
     * @param $side R°, V° (two sides)
     * @param $fragment, A, B, C, D, E, F, G, H (up to 8 letter coded fragements) or number
     * @param $column, i.e. I, II, III, IV, V, VI, VII, ...
     * @param $line, i.e. 1, 2, 3, 4, 5, 6, ... ∞; e.g. p.mich/p.mich.4.1/p.mich.4.1.224.xml: <lb n='6368'/>
     */
    protected function generateSytemSort($edition, $text, $page, $side, $fragment, $column, $line){

      if($column != null && array_key_exists($column, self::$ROMAN)){
        $column = self::$ROMAN[$column];
      }
      
      if($page != null && array_key_exists($page, self::$ROMAN)){
        $page = self::$ROMAN[$page];
      }

      if(in_array($edition, array('580000', '581000', '582000', '1250000'))){ // P. Lond 1 - 3 and O. Tait 1, sort by page and then by text
        return 'e' . $this->lpad($edition) .
               'p' . $this->lpad($page) .
               't' . $this->lpad($text) .
               's' . $this->lpad($side, 2) .
               'f' . $this->lpad($fragment) .
               'c' . $this->lpad($column) .
               'l' . $this->lpad($line);
      }

      return 'e' . $this->lpad($edition) .
             't' . $this->lpad($text) .
             'p' . $this->lpad($page) .
             's' . $this->lpad($side, 2) .
             'f' . $this->lpad($fragment) .
             'c' . $this->lpad($column) .
             'l' . $this->lpad($line);
    }

    protected function lpad($string, $length = 8, $pad = '0'){
      return str_pad($string, $length, $pad, STR_PAD_LEFT);
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
     * @var integer $sortPage
     */
    private $sortPage;

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
     * @var string $sortSystem
     */
    private $sortSystem;

    /**
     * @var string $sortUser
     */
    private $sortUser;

    /**
     * @var string $sort
     */
    private $sort;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
    public function getDescription($xml = false)
    {
        if($xml){
          return str_replace(array('<', '>', '&'), array('&gt;', '&lt;', '&amp;'), $this->description);
        }
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
     * Set sortPage
     *
     * @param integer $sortPage
     */
    public function setSortPage($sortPage)
    {
        $this->sortPage = $sortPage;
    }

    /**
     * Get sortPage
     *
     * @return integer 
     */
    public function getSortPage()
    {
        return $this->sortPage;
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
     * @param string $sortSystem
     */
    public function setSortSystem($sortSystem)
    {
        $this->sortSystem = $sortSystem;
    }

    /**
     * Get sortSystem
     *
     * @return string 
     */
    public function getSortSystem()
    {
        return $this->sortSystem;
    }

    /**
     * Set sortUser
     *
     * @param string $sortUser
     */
    public function setSortUser($sortUser)
    {
        $this->sortUser = $sortUser;
    }

    /**
     * Get sortUser
     *
     * @return string 
     */
    public function getSortUser()
    {
        return $this->sortUser;
    }

    /**
     * Set sort
     *
     * @param string $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * Get sort
     *
     * @return string 
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
    /**
     * @var string $compilationPage
     */
    private $compilationPage;


    /**
     * Set compilationPage
     *
     * @param string $compilationPage
     */
    public function setCompilationPage($compilationPage)
    {
        $this->compilationPage = $compilationPage;
    }

    /**
     * Get compilationPage
     *
     * @return string 
     */
    public function getCompilationPage()
    {
        return $this->compilationPage;
    }
}