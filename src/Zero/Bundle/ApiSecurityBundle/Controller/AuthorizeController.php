<?php

namespace Zero\Bundle\ApiSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zero\Bundle\ApiSecurityBundle\Entity\Client;
use Zero\Bundle\ApiSecurityBundle\Form\Model\Authorize;

class AuthorizeController extends Controller
{
    public function authorizeAction(Request $request)
    {
        if (!$request->get('client_id')) {
            throw new NotFoundHttpException("Client id parameter {$request->get('client_id')} is missing.");
        }

        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->findClientByPublicId($request->get('client_id'));

        if (!($client instanceof Client)) {
            throw new NotFoundHttpException("Client {$request->get('client_id')} is not found.");
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        $form = $this->createForm($this->container->get('zero_api_security.authorize.form_type'));
        $formHandler = $this->container->get('zero_api_security.authorize.form_handler');
        $formHandler->setForm($form);

        $authorize = new Authorize();

        if (($response = $formHandler->process($authorize)) !== false) {
            return $response;
        }

        return $this->container->get('templating')->renderResponse(
            'ZeroApiSecurityBundle:Authorize:authorize.html.twig',
            array(
                'form' => $form->createView(),
                'client' => $client,
            )
        );
    }
}
