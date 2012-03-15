<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Task;
use Papyrillio\BeehiveBundle\Entity\IndexEntry;

class BeehiveController extends Controller{
  protected function getParameter($key){
    $get  = $this->getRequest()->query->get($key);
    $post = $this->getRequest()->request->get($key);
    
    if($post && (is_array($post) || strlen(trim($post)))){
      return $post;
    }

    return $get;
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
