<?php

namespace Papyrillio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller{
  protected function getParameter($key){
    return strlen($this->getRequest()->request->get($key)) ? $this->getRequest()->request->get($key) : $this->getRequest()->query->get($key);
  }
}
