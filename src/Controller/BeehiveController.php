<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;
use App\Entity\Task;
use App\Entity\IndexEntry;

class BeehiveController extends AbstractController{
  protected $request;
  protected $allParameters = [];
  protected $logger;

  public function __construct(RequestStack $requestStack, LoggerInterface $logger)
  {
      $this->request = $requestStack->getCurrentRequest();
      $this->allParameters = array_merge($this->request->request->all(), $this->request->query->all());
      $this->logger = $logger;
  }

  protected function getParameter($key){
    if(array_key_exists($key, $this->allParameters)){
      return $this->allParameters[$key];
    }
    return null;
  }

  protected function getForm($object){
    if($object instanceof IndexEntry){
      return $this->createFormBuilder($object)
        ->add('type', 'choice', array('label' => 'Kategorie', 'choices' => self::getIndexTypes()))
        ->add('topic', 'choice', array('label' => 'Thema', 'choices' => self::getIndexTopics()))
        ->add('phrase', 'textarea', array('label' => 'Beschreibung'))
        ->getForm();
    } else if($object instanceof Task){
      return $this->createFormBuilder($object)
        ->add('category', 'choice', array('label' => 'Kategorie', 'choices' => self::getTaskCategories()))
        ->add('description', 'textarea', array('label' => 'Beschreibung'))
        ->getForm();
    }
    return null;
  }

  public static function getEditionMaterials(){
    return array('Ostrakon' => 'Ostrakon', 'Papyrus' => 'Papyrus');
  }

  public static function getIndexTypes(){
    return array('Neues Wort' => 'Neues Wort', 'Ghostword' => 'Ghostword');
  }

  public static function getIndexTopics(){
    return array('Personennamen' => 'Personennamen', 'Könige, Kaiser, Konsuln' => 'Könige, Kaiser, Konsuln', 'Geographisches und Topographisches' => 'Geographisches und Topographisches', 'Monate und Tage' => 'Monate und Tage', 'Religion' => 'Religion', 'Zivil- und Militärverwaltung' => 'Zivil- und Militärverwaltung', 'Steuern' => 'Steuern', 'Berufsbezeichnungen' => 'Berufsbezeichnungen', 'Allgemeiner Wortindex' => 'Allgemeiner Wortindex');
  }

  public static function getTaskCategories(){
    return array('apis' => 'APIS', 'biblio' => 'Biblio', 'bl' => 'BL', 'ddb' => 'DDB', 'hgv' => 'HGV', 'tm' => 'TM');
  }

  public static function getTaskCategoryKeys(){
    return array_keys(self::getTaskCategories());
  }
}
