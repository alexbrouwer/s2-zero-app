<?php


namespace Zero\ApiBaseBundle\Tests\Manager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class EntityManagerTestFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'test',
            'text',
            array(
                'constraints' => array(
                    new NotBlank()
                )
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
                'data_class' => 'Zero\ApiBaseBundle\Tests\Manager\EntityManagerTestEntity'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'entity-manager-test';
    }
} 