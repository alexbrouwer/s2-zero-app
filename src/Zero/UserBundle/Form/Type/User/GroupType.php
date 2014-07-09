<?php

namespace Zero\UserBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add(
                'roles',
                'collection',
                array(
                    'type'         => 'text',
                    'allow_add'    => true,
                    'allow_delete' => true
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Zero\UserBundle\Entity\User\Group'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user_group';
    }
}
