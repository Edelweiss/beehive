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

}
