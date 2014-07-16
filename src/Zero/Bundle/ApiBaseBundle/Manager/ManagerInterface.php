<?php


namespace Zero\Bundle\ApiBaseBundle\Manager;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

interface ManagerInterface
{
    /**
     * @param string $entityClass
     * @param string|FormTypeInterface $form Form service alias, Form class or actual form instance
     */
    public function __construct($entityClass, $form);

    /**
     * Set object manager
     *
     * @param ObjectManager $objectManager
     *
     * @return ManagerInterface
     */
    public function setObjectManager(ObjectManager $objectManager);

    /**
     * Set form factory
     *
     * @param FormFactoryInterface $formFactory
     *
     * @return ManagerInterface
     */
    public function setFormFactory(FormFactoryInterface $formFactory);
}