<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Edition;
use DateTime;

class EditionController extends BeehiveController{

  public function listAction(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');
    $editions = array();
    
    if ($this->getRequest()->getMethod() == 'POST') {
      
      // PARAMETERS
      
      $limit         = $this->getParameter('rows');
      $page          = $this->getParameter('page');
      $offset        = $page * $limit - $limit;
      $offset        = $offset < 0 ? 0 : $offset;
      $sort          = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');

      // ODER BY
      
      $orderBy = ' ORDER BY e.' . $sort . ' ' . $sortDirection;

      // WHERE
      
      $where = '';
      if($this->getParameter('_search') == 'true'){
        $where = '';
        $prefix = ' WHERE ';

        foreach(array('sort', 'title', 'collection', 'volume', 'remark', 'material') as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'e.' . $field . ' LIKE \'%' . $this->getParameter($field) . '%\'';
            $prefix = ' AND ';
          }
        }
      }

      // LIMIT

      $query = $entityManager->createQuery('SELECT count(e.id) FROM PapyrillioBeehiveBundle:Edition e ' . $where);
      $count = $query->getSingleScalarResult();
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;

      // QUERY
      
      $query = $entityManager->createQuery('SELECT e FROM PapyrillioBeehiveBundle:Edition e ' . $where . ' ' . $orderBy)->setFirstResult($offset)->setMaxResults($limit);
      
      $editions = $query->getResult();

      return $this->render('PapyrillioBeehiveBundle:Edition:list.xml.twig', array('editions' => $editions, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page));
    } else {
      return $this->render('PapyrillioBeehiveBundle:Edition:list.html.twig', array('editions' => $editions));
    }
  }
  
  public function newAction(){
    $edition = new Edition();
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');

    $form = $this->createFormBuilder($edition)
      ->add('sort', 'text', array('label' => 'Sortierung'))
      ->add('title', 'text', array('label' => 'Titel'))
      ->add('remark', 'text', array('required' => false, 'label' => 'Bemerkung'))
      ->add('material', 'choice', array('choices' => array('Papyrus' => 'Papyrus', 'Ostrakon' => 'Ostrakon')))
      ->getForm();

    if ($this->getRequest()->getMethod() == 'POST') {

      $form->bindRequest($this->getRequest());

      if ($form->isValid()) {
        $entityManager->persist($edition);
        $entityManager->flush();

        $this->get('session')->setFlash('notice', 'Die Edition »' . $edition->getSort() . ' = ' . $edition->getTitle() . '« wurde angelegt.');

        return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_editionlist'));
      }
    }

    return $this->render('PapyrillioBeehiveBundle:Edition:new.html.twig', array('form' => $form->createView()));
  }

  public function updateAction(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');
    $edition = $repository->findOneBy(array('id' => $this->getParameter('id')));
    
    foreach(array('sort', 'title', 'remark', 'material') as $field){
      if($value = $this->getParameter($field)){
        $setter = 'set' . ucfirst($field);
        $getter = 'get' . ucfirst($field);

        $edition->$setter($value);
      }

    }

    $entityManager->flush();

    return new Response($edition);
  }

  public function deleteAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');
    $edition = $repository->findOneBy(array('id' => $id));

    $entityManager->remove($edition);
    $entityManager->flush();
    return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_editionlist'));
  }

  public function showAction($id){

    if(!$id){
      return $this->forward('PapyrillioBeehiveBundle:Edition:list');
    }
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Edition');
    $edition = $repository->findOneBy(array('id' => $id));

    return $this->render('PapyrillioBeehiveBundle:Edition:show.html.twig', array('edition' => $edition));
  }

}
