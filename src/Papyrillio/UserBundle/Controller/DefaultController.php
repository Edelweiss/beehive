<?php

namespace Papyrillio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\UserBundle\Entity\User;
use DateTime;

class DefaultController extends UserController
{
  public function tadaimaAction(){
    $user = $this->get('security.context')->getToken()->getUser();
    $user->setLastLogin($user->getCurrentLogin());
    $user->setCurrentLogin(new DateTime('now'));
    $entityManager = $this->getDoctrine()->getEntityManager();
    $entityManager->flush();
    
    return $this->redirect($this->generateUrl('PapyrillioBeehiveBundle_home'));
  }
  
  public function listAction(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioUserBundle:User');
    
    if ($this->getRequest()->getMethod() == 'POST') {
      
      // PARAMETERS
      
      $limit         = $this->getParameter('rows');
      $page          = $this->getParameter('page');
      $offset        = $page * $limit - $limit;
      $offset        = $offset < 0 ? 0 : $offset;
      $sort          = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');

      // ODER BY
      
      $orderBy = ' ORDER BY u.' . $sort . ' ' . $sortDirection;

      // WHERE
      
      $where = '';
      if($this->getParameter('_search') == 'true'){
        $where = '';
        $prefix = ' WHERE ';
        
        foreach(array('username', 'email', 'name', 'lastLogin') as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'u.' . $field . ' LIKE \'%' . $this->getParameter($field) . '%\'';
            $prefix = ' AND ';
          }
        }
      }
      
      // LIMIT

      $query = $entityManager->createQuery('SELECT count(u.id) FROM PapyrillioUserBundle:User u' . $where);
      $count = $query->getSingleScalarResult();
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;

      // QUERY
      
      $query = $entityManager->createQuery('SELECT u FROM PapyrillioUserBundle:User u ' . $where . ' ' . $orderBy)->setFirstResult($offset)->setMaxResults($limit);
      
      $users = $query->getResult();

      return $this->render('PapyrillioUserBundle:Default:list.xml.twig', array('users' => $users, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page));
    } else {
      return $this->render('PapyrillioUserBundle:Default:list.html.twig');
    }
  }

  public function showAction($id){

    if(!$id){
      return $this->forward('PapyrillioUserBundle:Default:list');
    }
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioUserBundle:User');
    $user = $repository->findOneBy(array('id' => $id));

    return $this->render('PapyrillioUserBundle:Default:show.html.twig', array('user' => $user));
  }

  public function updateAction(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $user = $this->get('security.context')->getToken()->getUser();
    
    $setter = 'set' . ucfirst($this->getParameter('elementid'));
    $getter = 'get' . ucfirst($this->getParameter('elementid'));
    
    $user->$setter($this->getParameter('newvalue'));
    $entityManager->flush();
    
    return new Response($user->$getter());
  }

  public function passwordAction(){
    $user = $this->get('security.context')->getToken()->getUser();

    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioUserBundle:User');

    $form = $this->createFormBuilder($user)
      ->add('password', 'text', array('label' => 'Neues Passwort'))
      ->getForm();

    if ($this->getRequest()->getMethod() == 'POST') {
        
      $form->bindRequest($this->getRequest());

      if ($form->isValid()) {

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));

        $entityManager->persist($user);
        $entityManager->flush();

        $this->get('session')->setFlash('notice', 'Das Passwort für Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde geändert.');

        return $this->redirect($this->generateUrl('PapyrillioUserBundle_show', array('id' => $user->getId())));
      }
    }

    return $this->render('PapyrillioUserBundle:Default:password.html.twig', array('form' => $form->createView()));
  }

  public function newAction(){
    $user = new User();
    $user->setRoles(array('ROLE_USER'));
    
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioUserBundle:User');

    $form = $this->createFormBuilder($user)
      ->add('name', 'text', array('label' => 'Name'))
      ->add('username', 'text', array('label' => 'Kennung'))
      ->add('password', 'text', array('label' => 'Passwort'))
      ->add('email', 'text', array('label' => 'E-Mail'))
      ->getForm();

    if ($this->getRequest()->getMethod() == 'POST') {
        
      $form->bindRequest($this->getRequest());

      if ($form->isValid()) {

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        $this->get('session')->setFlash('notice', 'Der Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde angelegt.');

        return $this->redirect($this->generateUrl('PapyrillioUserBundle_show', array('id' => $user->getId())));
      }
    }

    return $this->render('PapyrillioUserBundle:Default:new.html.twig', array('form' => $form->createView()));
  }

  public function deleteAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioUserBundle:User');
    if($user = $repository->findOneBy(array('id' => $id))){
      $entityManager->remove($user);
      $entityManager->flush();
      $this->get('session')->setFlash('notice', 'Der Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde gelöscht.');
    }
    return $this->redirect($this->generateUrl('PapyrillioUserBundle_list'));
  }

  public function resetAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioUserBundle:User');
    if($user = $repository->findOneBy(array('id' => $id))){
      $encoder = $this->get('security.encoder_factory')->getEncoder($user);
      $user->setPassword($encoder->encodePassword('changeYourPasswordASAP', $user->getSalt()));
      $entityManager->flush();
      $this->get('session')->setFlash('notice', 'Das Passwort für Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde zurückgesetzt.');
    }
    return $this->redirect($this->generateUrl('PapyrillioUserBundle_show', array('id' => $id)));
  }
}
