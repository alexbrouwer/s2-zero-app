<?php

namespace Zero\ApiDocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ZeroApiDocBundle:Default:index.html.twig');
    }
}
