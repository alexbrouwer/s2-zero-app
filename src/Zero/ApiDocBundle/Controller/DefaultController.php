<?php

namespace Zero\ApiDocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $extractor = $this->container->get('zero_api_doc.extractor');

        var_dump($extractor->all());
        die;

        return $this->render('ZeroApiDocBundle:Default:index.html.twig');
    }
}
