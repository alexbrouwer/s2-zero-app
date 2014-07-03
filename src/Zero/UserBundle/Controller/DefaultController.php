<?php

namespace Zero\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name = 'test')
    {
        return $this->render('ZeroUserBundle:Default:index.html.twig', array('name' => $name));
    }
}
