<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BeehiveController extends Controller{
  protected function getParameter($key){
    return $this->getRequest()->request->get($key) ? $this->getRequest()->request->get($key) : $this->getRequest()->query->get($key);
  }
}
