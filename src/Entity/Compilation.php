<?php
namespace App\Entity;

use App\Repository\CompilationRepository;
use Doctrine\ORM\Mapping as ORM;

class Compilation
{
    /**
     * Set volume
     *
     * @param integer $volume
     */
    public function setVolume($volume){
        $this->volume = $volume;
        $this->updateTitle();
    }

    /**
    * Set fascicle
    *
    * @param integer $fascicle
    */
    public function setFascicle($fascicle){
        $this->fascicle = $fascicle;
        $this->updateTitle();
    }
    
    protected function updateTitle(){
      $this->title = $this->numberToRoman($this->volume) . ($this->fascicle ? ' ' . $this->fascicle : '');
    }

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $volume
     */
    private $volume;

    /**
     * @var integer $fascicle
     */
    private $fascicle;

    /**
     * @var text $title
     */
    private $title;

    /**
     * @var date $start
     */
    private $start;

    /**
     * @var date $end
     */
    private $end;

    /**
     * @var datetime $publication
     */
    private $publication;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Correction
     */
    private $corrections;

    public function __construct()
    {
        $this->corrections = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Get fascicle
     *
     * @return integer 
     */
    public function getFascicle()
    {
        return $this->fascicle;
    }

    /**
     * Set title
     *
     * @param text $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * Get volume
     *
     * @return integer 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set start
     *
     * @param date $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start
     *
     * @return date 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param date $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end
     *
     * @return date 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set publication
     *
     * @param datetime $publication
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;
    }

    /**
     * Get publication
     *
     * @return datetime 
     */
    public function getPublication()
    {
        return $this->publication;
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
    
    protected function numberToRoman($num)
    {
       // Make sure that we only use the integer portion of the value
       $n = intval($num);
       $result = '';
   
       // Declare a lookup array that we will use to traverse the number:
       $lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
       'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
       'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
   
       foreach ($lookup as $roman => $value) 
       {
           // Determine the number of matches
           $matches = intval($n / $value);
   
           // Store that many characters
           $result .= str_repeat($roman, $matches);
   
           // Substract that from the number
           $n = $n % $value;
       }
   
       // The Roman numeral should be built, return it
       return $result;
    } // http://www.go4expert.com/forums/showthread.php?t=4948
}