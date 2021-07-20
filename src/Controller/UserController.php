<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserEditProfileType;
use App\Form\UserChangePasswordType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserController extends BeehiveController
{

    public function editProfile(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        //if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        //}

        $user = $this->getUser();
        $form = $this->createForm(UserEditProfileType::class, $user);

        //$form->handleRequest($request);
        if($formData = $this->getParameter('user_edit_profile')){
            $pwd = $formData['password'];
            $checkPass = $passwordEncoder->isPasswordValid($user, $pwd);
            if($checkPass === true) {
                $formDummy = $this->createForm(UserEditProfileType::class, new User());
                $formDummy->handleRequest($request);
                if ($formDummy->isSubmitted() && $formDummy->isValid()) {
                    // Save
                    $user->setUsername($formData['username']);
                    $user->setEmail($formData['email']);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('notice', 'Perfect');
                    $form = $this->createForm(UserEditProfileType::class, $user); // cl: Why do I need to create the form anew to make it reflect the persisted changes of its user object in the template?! Whithout this line, it will reflect the user object’s status before the update.
                } else {
                    $this->addFlash('error', 'Invalid form');
                }
            } else {
                $this->addFlash('error', 'Wrong password');
            }
        }

        /*if ($form->isSubmitted() && $form->isValid()) {
            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }*/

        return $this->render('user/editProfile.html.twig', ['form' => $form->createView()]);
    }

    public function changePassword(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserChangePasswordType::class, $user);

        if($this->getParameter('user_change_password')){
            if($this->getParameter('password') && $passwordEncoder->isPasswordValid($user, $this->getParameter('password')) === true){
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // Save
                    $formData = $this->getParameter('user_change_password');
                    $user->setPassword($passwordEncoder->encodePassword($user, $formData['password']['first']));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('notice', 'Perfect');
                } else {
                    $this->addFlash('error', 'Invalid form');
                }
            } else {
                $this->addFlash('error', 'Wrong password');
            }
        }

        return $this->render('user/changePassword.html.twig', ['form' => $form->createView()]);
    }
  
    public function list(): Response {
      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(User::class);

      if ($this->request->getMethod() == 'POST') {

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

          foreach(['username', 'email', 'name', 'lastLogin'] as $field){
            if(strlen($this->getParameter($field))){
              $where .= $prefix . 'u.' . $field . ' LIKE \'%' . $this->getParameter($field) . '%\'';
              $prefix = ' AND ';
            }
          }
        }

        // LIMIT

        $query = $entityManager->createQuery('SELECT count(u.id) FROM App\Entity\User u' . $where);
        $count = $query->getSingleScalarResult();
        $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;

        // QUERY

        $query = $entityManager->createQuery('SELECT u FROM App\Entity\User u ' . $where . ' ' . $orderBy)->setFirstResult($offset)->setMaxResults($limit);

        $users = $query->getResult();

        return $this->render('user/list.xml.twig', ['users' => $users, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page]);
      } else {
        return $this->render('user/list.html.twig');
      }
    }

    public function show($id): Response {
  
      if(!$id){
        return $this->forward('user/list');
      }

      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(User::class);
      $user = $repository->findOneBy(['id' => $id]);
  
      return $this->render('user/show.html.twig', ['user' => $user]);
    }
  
    public function update(): Response {
      $entityManager = $this->getDoctrine()->getManager();
      $user = $this->get('security.context')->getToken()->getUser();
      
      $setter = 'set' . ucfirst($this->getParameter('elementid'));
      $getter = 'get' . ucfirst($this->getParameter('elementid'));
      
      $user->$setter($this->getParameter('newvalue'));
      $entityManager->flush();
      
      return new Response($user->$getter());
    }
  
    public function password(): Response {
      $user = $this->get('security.context')->getToken()->getUser();
  
      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(User::class);
  
      $form = $this->createFormBuilder($user)
        ->add('password', 'password', ['label' => 'Neues Passwort'])
        ->getForm();
  
      if ($this->getRequest()->getMethod() == 'POST') {
  
        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
  
          $encoder = $this->get('security.encoder_factory')->getEncoder($user);
          $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
  
          $entityManager->persist($user);
          $entityManager->flush();
  
          $this->get('session')->setFlash('notice', 'Das Passwort für Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde geändert.');
  
          return $this->redirect($this->generateUrl('PapyrillioBeehive_UserShow', ['id' => $user->getId()]));
        }
      }
  
      return $this->render('user/password.html.twig', ['form' => $form->createView()]);
    }
  
    public function new(): Response {
      $user = new User();
      $user->setRoles(['ROLE_USER']);

      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(User::class);

      $form = $this->createFormBuilder($user)
        ->add('name', 'text', ['label' => 'Name'])
        ->add('username', 'text', ['label' => 'Kennung'])
        ->add('password', 'text', ['label' => 'Passwort'])
        ->add('email', 'text', ['label' => 'E-Mail'])
        ->getForm();

      if ($this->getRequest()->getMethod() == 'POST') {
          
        $form->bindRequest($this->getRequest());
  
        if ($form->isValid()) {

          $encoder = $this->get('security.encoder_factory')->getEncoder($user);
          $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));

          $entityManager->persist($user);
          $entityManager->flush();

          $this->get('session')->setFlash('notice', 'Der Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde angelegt.');

          return $this->redirect($this->generateUrl('PapyrillioBeehive_UserShow', ['id' => $user->getId()]));
        }
      }

      return $this->render('user/new.html.twig', ['form' => $form->createView()]);
    }
  
    public function delete($id): Response{
      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(User::class);
      if($user = $repository->findOneBy(['id' => $id])){
        $entityManager->remove($user);
        $entityManager->flush();
        $this->get('session')->setFlash('notice', 'Der Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde gelöscht.');
      }
      return $this->redirect($this->generateUrl('PapyrillioBeehive_UserList'));
    }
  
    public function reset($id): Response {
      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(User::class);
      if($user = $repository->findOneBy(['id' => $id])){
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword('changeYourPasswordASAP', $user->getSalt()));
        $entityManager->flush();
        $this->get('session')->setFlash('notice', 'Das Passwort für Benutzer ' . $user->getName() . ' (' . $user->getUsername() . ') wurde zurückgesetzt.');
      }
      return $this->redirect($this->generateUrl('PapyrillioBeehive_UserShow', ['id' => $id]));
    }

}
