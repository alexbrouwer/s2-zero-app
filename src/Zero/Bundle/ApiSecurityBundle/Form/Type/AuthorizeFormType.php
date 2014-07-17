<?php

namespace Zero\Bundle\ApiSecurityBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AuthorizeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'allowAccess',
            'checkbox',
            array(
                'label' => 'Allow access',
            )
        );
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Zero\Bundle\ApiSecurityBundle\Form\Model\Authorize');
    }

    public function getName()
    {
        return 'zero_api_security_authorize';
    }
} 