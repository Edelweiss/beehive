<?php

namespace App\Entity;

use App\Repository\CorrectionRepository;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Loggable;
use Doctrine\ORM\Event\LifecycleEventArgs; // prePersist
use Doctrine\ORM\Event\OnFlushEventArgs; // onFlush
use Doctrine\ORM\Event\PreUpdateEventArgs; // preUpdate
use DateTime;
use Exception;

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

    const MODE_XML         = 'xml';
    const MODE_OOXML       = 'ooxml';

    const MODE_PLAIN       = 'plain';
    const MODE_REGISTER    = 'register';

    public static $STATUS = array('unchecked', 'reviewed', 'finalised');
    public static $ROMAN  = array('I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12, 'XIII' => 13, 'XIV' => 14, 'XV' => 15, 'XVI' => 16, 'XVII' => 17, 'XVIII' => 18, 'XIX' => 19, 'XX' => 20, 'XXI' => 21, 'XXII' => 22, 'XXIII' => 23, 'XXIV' => 24, 'XXV' => 25, 'XXVI' => 26, 'XXVII' => 27, 'XXVIII' => 28, 'XXIX' => 29, 'XXX' => 30, 'XXXI' => 31, 'XXXII' => 32, 'XXXIII' => 33, 'XXXIV' => 34, 'XXXV' => 35, 'XXXVI' => 36, 'XXXVII' => 37, 'XXXIX' => 39, 'XXXVIII' => 38, 'XL' => 40, 'XLI' => 41, 'XXIX' => 42, 'XLIII' => 43, 'XLIV' => 44, 'XLV' => 45, 'XLVI' => 46, 'XLVII' => 47, 'XLVIII' => 48, 'XLIX' => 49, 'L' => 50, 'LI' => 51, 'LII' => 52, 'LIII' => 53, 'LIV' => 54, 'LV' => 55, 'LVI' => 56, 'LVII' => 57, 'LVIII' => 58, 'LIX' => 59, 'LX' => 60, 'LXI' => 61, 'LXII' => 62, 'LXIII' => 63, 'LXIV' => 64, 'LXV' => 65, 'LXVI' => 66, 'LXVII' => 67, 'LXVIII' => 68, 'LXIX' => 69, 'LXX' => 70, 'LXXI' => 71, 'LXXII' => 72, 'LXXIII' => 73, 'LXXIV' => 74, 'LXXV' => 75, 'LXXVI' => 76, 'LXXVII' => 77, 'LXXVIII' => 78, 'LXXIX' => 79, 'LXXX' => 80, 'LXXXI' => 81, 'LXXXII' => 82, 'LXXXIII' => 83, 'LXXXIV' => 84, 'LXXXV' => 85, 'LXXXVI' => 86, 'LXXXVII' => 87, 'LXXXVIII' => 88, 'LXXXIX' => 89, 'XC' => 90, 'XCI' => 91, 'XCII' => 92, 'XCIII' => 93, 'XCIV' => 94, 'XCV' => 95, 'XCVI' => 96, 'XCVII' => 97, 'XCVIII' => 98, 'XCIX' => 99, 'C' => 100);
    public static $ALPHA  = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8);

    public static $ENCODE = array(
      '𝈪' => 'f09d88aa',
      '𐅵' => 'f09085b5', // one half
      '𐅷' => 'f09085b7', // two thirds
      '𐅸' => 'f09085b8' // three quarters
    );
  
    public static $DECODE = array(
      'f09d88aa' => '𝈪',
      'f09085b5' => '𐅵',
      'f09085b7' => '𐅷',
      'f09085b8' => '𐅸'
    );

    public static function encode4Byte($data){
      if(is_array($data)){
        foreach($data as $key => $value){
          $data[$key] = self::encode4ByteUnicode($value);
        }
        return $data;
      } else {
        return self::encode4ByteUnicode($data);
      }
    }

    public static function decode4Byte($data){
      if(is_array($data)){
        foreach($data as $key => $value){
          $data[$key] = self::decode4ByteUnicode($value);
        }
        return $data;
      } else {
        return self::decode4ByteUnicode($data);
      }
    }

    public static function encode4ByteUnicode($string){
      mb_internal_encoding('UTF-8');
      mb_regex_encoding('UTF-8');
      foreach(self::$ENCODE as $character => $code){
        if(mb_strpos($string, $character) !== FALSE){
          $string = mb_ereg_replace($character, $code, $string);
        }
      }
      return $string;
    }
    
    public static function decode4ByteUnicode($string){
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
        $this->registerEntries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->indexEntries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = self::STATUS_UNCHECKED;
        $this->creator = 'system';
        $this->sort = $this->sortSystem = '';
        $this->created = new DateTime('now');
        $this->text = '';
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

    public function setDdb($ddb)
    {
        throw new Exception('setDdb() invalid function');
    }

    public function setTm($tm)
    {
        throw new Exception('setTm() invalid function');
    }

    public function setHgv($hgv)
    {
        throw new Exception('setHgv() invalid function');

    }

    public function setStatus($status)
    {
        if(in_array($status, self::$STATUS)){
          $this->status = $status;
        } else {
          $this->status = self::STATUS_DEFAULT;
        }
    }

    public function setText($text = ''){
      $this->text = $text;
      $this->setSortValues();
    }

    public function setPosition($position){
      $this->position = $position;
      $this->setSortValues();
    }

    public function setEdition(\App\Entity\Edition $edition){
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

        //echo '<pre>';
        //var_dump($sortText);
        //echo '</pre>';

        if(preg_match('/^(\d+)([\-+ .,]*(\d+))*(( | Fr. )?\(?([*a-zA-Z])((-|\+| |, ?)[*a-zA-Z])*\)?)?( \((\d+)(-\d+)?\)(, \(\d+\))*)?( ([RV]°))?( \(S\. (\d+)\))?( konkave Innenseite)?$/', $sortText, $matches)){
        //               1    2         3      45            6          78                           9  10    11       12            13 14      15       16

                //echo '<pre>';
        //var_dump($matches);
        //echo '</pre>';
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
     * Key  e........p........t........s..f........c........l........
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

      //if(in_array($edition, array('580000', '581000', '582000', '1250000'))){ // P. Lond 1 - 3 and O. Tait 1, sort by page and then by text
      if($edition === '1250000'){ // O. Tait 1, sort by page and then by text
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
      if($this->registerEntries and $this->registerEntries->first()){
        return $this->registerEntries->first()->getLinks();
      }
      return array();
    }

    private $id;
    private $source;
    private $text;
    private $position;
    private $description;
    private $status;
    private $creator;
    private $created;
    private $sortPage;
    private $sortSide;
    private $sortFragment;
    private $sortColumn;
    private $sortLine;
    private $sortSystem;
    private $sortUser;
    private $sort;
    private $registerEntries;
    private $tasks;
    private $compilation;
    private $edition;
    public function getId()
    {
        return $this->id;
    }
    public function setSource($source)
    {
        $this->source = $source;
    }
    public function getSource()
    {
        return $this->source;
    }
    public function getText()
    {
        return $this->text;
    }
    public function getTm()
    {
      if($this->registerEntries and $this->registerEntries->first()){
        return $this->registerEntries->first()->getTm();
      }
      return null;
    }
    public function getHgv()
    {
      if($this->registerEntries and $this->registerEntries->first()){
        return $this->registerEntries->first()->getHgv();
      }
      return null;
    }
    public function getDdb()
    {
      $registerList = $this->getDistinctDdb(self::MODE_REGISTER);
      if(count($registerList)){
        return array_pop($registerList)->getDdb(); 
      }
      return null;
    }
    public function getDdbCollection()
    {
      if($registerList = $this->getDistinctDdb(self::MODE_REGISTER)){
        return array_pop($registerList)->getDdbCollection(); 
      }
      return null;
    }
    public function getDdbVolume()
    {
      if($registerList = $this->getDistinctDdb(self::MODE_REGISTER)){
        return array_pop($registerList)->getDdbVolume(); 
      }
      return null;
    }
    public function getDdbDocument()
    {
      if($registerList = $this->getDistinctDdb(self::MODE_REGISTER)){
        return array_pop($registerList)->getDdbDocument(); 
      }
      return null;
    }
    public function getDclp()
    {
      if($registerList = $this->getDistinctDclp(self::MODE_REGISTER)){
        return array_pop($registerList)->getDclp(); 
      }
      return null;
    }
    public function getDclpCollection()
    {
      if($registerList = $this->getDistinctDclp(self::MODE_REGISTER)){
        return $registerList[0]->getDclpCollection(); 
      }
      return null;
    }
    public function getDclpVolume()
    {
      if($registerList = $this->getDistinctDclp(self::MODE_REGISTER)){
        return $registerList[0]->getDclpVolume(); 
      }
      return null;
    }
    public function getDclpDocument()
    {
      if($registerList = $this->getDistinctDclp(self::MODE_REGISTER)){
        return $registerList[0]->getDclpDocument(); 
      }
      return null;
    }
    public function getPosition()
    {
        return $this->position;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function getDescription($mode = null)
    {
        if($mode == self::MODE_XML){
          return str_replace(array('<', '>', '&'), array('&gt;', '&lt;', '&amp;'), $this->description);
        } else if($mode == self::MODE_OOXML){
          $ooxml = $this->description;
          $ooxml = mb_ereg_replace('<', '#lt;',$ooxml);
          $ooxml = mb_ereg_replace('>', '#gt;',$ooxml);
          $ooxml = mb_ereg_replace('&', '#amp;',$ooxml);
          $ooxml = mb_ereg_replace('#lt;', '<text:span>&lt;</text:span>',$ooxml);
          $ooxml = mb_ereg_replace('#gt;', '<text:span>&gt;</text:span>',$ooxml);
          $ooxml = mb_ereg_replace('#amp;', '<text:span>&amp;</text:span>',$ooxml);
          return $ooxml;
        }
        return $this->description;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }
    public function getCreator()
    {
        return $this->creator;
    }
    public function setCreated($created)
    {
        $this->created = $created;
    }
    public function getCreated()
    {
        return $this->created;
    }
    public function setSortPage($sortPage)
    {
        $this->sortPage = $sortPage;
    }
    public function getSortPage()
    {
        return $this->sortPage;
    }
    public function setSortSide($sortSide)
    {
        $this->sortSide = $sortSide;
    }
    public function getSortSide()
    {
        return $this->sortSide;
    }
    public function setSortFragment($sortFragment)
    {
        $this->sortFragment = $sortFragment;
    }
    public function getSortFragment()
    {
        return $this->sortFragment;
    }
    public function setSortColumn($sortColumn)
    {
        $this->sortColumn = $sortColumn;
    }
    public function getSortColumn()
    {
        return $this->sortColumn;
    }
    public function setSortLine($sortLine)
    {
        $this->sortLine = $sortLine;
    }
    public function getSortLine()
    {
        return $this->sortLine;
    }
    public function setSortSystem($sortSystem)
    {
        $this->sortSystem = $sortSystem;
    }
    public function getSortSystem()
    {
        return $this->sortSystem;
    }
    public function setSortUser($sortUser)
    {
        $this->sortUser = $sortUser;
    }
    public function getSortUser()
    {
        return $this->sortUser;
    }
    public function setSort($sort)
    {
        $this->sort = $sort;
    }
    public function getSort()
    {
        return $this->sort;
    }
    public function addRegisterEntry(\App\Entity\Register $registerEntries)
    {
        $this->registerEntries[] = $registerEntries;
    }
    public function getRegisterEntries()
    {
        return $this->registerEntries;
    }
    public function getDistinctTm($mode = self::MODE_PLAIN)
    {
        $distinctList = array();
        foreach($this->registerEntries as $registerEntry){
            if($registerEntry->getTm()){
              $distinctList[$registerEntry->getTm()] = $mode == self::MODE_PLAIN ? $registerEntry->getTm() : $registerEntry;
            }
        }
        return $distinctList;
    }

    public function getDistinctHgv($mode = self::MODE_PLAIN)
    {
        $distinctList = array();
        foreach($this->registerEntries as $registerEntry){
            if($registerEntry->getHgv($mode = self::MODE_PLAIN)){
              $distinctList[$registerEntry->getHgv()] = $mode == self::MODE_PLAIN ? $registerEntry->getHgv() : $registerEntry;
            }
        }
        return $distinctList;
    }

    public function getDistinctDdb($mode = self::MODE_PLAIN)
    {
        $distinctList = array();
        foreach($this->registerEntries as $registerEntry){
            if($registerEntry->getDdb()){
                $distinctList[$registerEntry->getDdb()] = $mode == self::MODE_PLAIN ? $registerEntry->getDdb() : $registerEntry;
            }
        }
        return $distinctList;
    }

    public function getDistinctDclp($mode = self::MODE_PLAIN)
    {
        $distinctList = array();
        foreach($this->registerEntries as $registerEntry){
            if($registerEntry->getDclp()){
              $distinctList[$registerEntry->getDclp()] = $mode == self::MODE_PLAIN ? $registerEntry->getDclp() : $registerEntry;
            }
        }
        return $distinctList;
    }
    public function addTask(\App\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;
    }
    public function setCompilation(\App\Entity\Compilation $compilation)
    {
        $this->compilation = $compilation;
    }
    public function getCompilation()
    {
        return $this->compilation;
    }
    public function getEdition()
    {
        return $this->edition;
    }
    private $indexEntries;
    public function addIndexEntry(\App\Entity\IndexEntry $indexEntries)
    {
        $this->indexEntries[] = $indexEntries;
    }
    public function getIndexEntries()
    {
        return $this->indexEntries;
    }
    private $compilationPage;
    public function setCompilationPage($compilationPage)
    {
        $this->compilationPage = $compilationPage;
    }
    public function getCompilationPage()
    {
        return $this->compilationPage;
    }
    private $compilationIndex;
    public function setCompilationIndex($compilationIndex)
    {
        $this->compilationIndex = $compilationIndex;
    }
    public function getCompilationIndex()
    {
        return $this->compilationIndex;
    }
}
