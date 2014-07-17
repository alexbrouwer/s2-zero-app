<?php


namespace Zero\Bundle\ApiSecurityBundle\Form\Handler;


use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Zero\Bundle\ApiSecurityBundle\Form\Model\Authorize;

class AuthorizeFormHandler
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $context;

    /**
     * @var \OAuth2\OAuth2
     */
    protected $oauth2;

    /**
     * @param Request $request
     * @param SecurityContextInterface $context
     * @param OAuth2 $oauth2
     */
    public function __construct(Request $request, SecurityContextInterface $context, OAuth2 $oauth2)
    {
        $this->request = $request;
        $this->context = $context;
        $this->oauth2 = $oauth2;
    }

    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @param Authorize $authorize
     * @return bool|\Symfony\Component\HttpFoundation\Response
     */
    public function process(Authorize $authorize)
    {
        $this->form->setData($authorize);

        if ($this->request->getMethod() == 'POST') {

            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {

                try {
                    $user = $this->context->getToken()->getUser();
                    return $this->oauth2->finishClientAuthorization(true, $user, $this->request, null);
                } catch (OAuth2ServerException $e) {
                    return $e->getHttpResponse();
                }

            }

        }

        return false;
    }
} 