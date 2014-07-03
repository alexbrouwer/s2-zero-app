<?php


namespace Zero\ApiBaseBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Zero\ApiBaseBundle\Exception\InvalidFormException;

class AbstractManager implements ManagerInterface
{
    const METHOD_PUT = 'PUT';

    const METHOD_POST = 'POST';

    const METHOD_PATCH = 'PATCH';

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var FormTypeInterface
     */
    protected $form;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param string $entityClass
     * @param string|FormTypeInterface $form Form service alias, Form class or actual form instance
     */
    public function __construct($entityClass, $form)
    {
        $this->entityClass = $entityClass;
        if (is_string($form) && class_exists($form)) {
            $form = new $form;
        }
        $this->form = $form;
    }

    /**
     * Set object manager
     *
     * @param ObjectManager $objectManager
     *
     * @return AbstractManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->om = $objectManager;

        return $this;
    }

    /**
     * Set form factory
     *
     * @param FormFactoryInterface $formFactory
     *
     * @return AbstractManager
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;

        return $this;
    }

    /**
     * Create form
     *
     * @param $entity
     * @param string $method
     *
     * @return FormInterface
     */
    public function createForm($entity, $method = self::METHOD_PUT)
    {
        if (is_string($this->form)) {
            return $this->formFactory->createNamed($this->form, 'form', $entity, array('method' => $method));
        }

        return $this->formFactory->create($this->form, $entity, array('method' => $method));
    }

    /**
     * Process entity parameters using a form
     *
     * @param object $entity
     * @param array $parameters
     * @param string $method
     *
     * @return object entity
     *
     * @throws InvalidFormException if parameters are not valid
     */
    public function processForm($entity, array $parameters, $method = self::METHOD_PUT)
    {
        $form           = $this->createForm($entity, $method);
        $formParameters = $this->getFormParameters($form, $parameters);
        $form->submit($formParameters, self::METHOD_PATCH !== $method);

        if ($form->isValid()) {
            return $this->saveEntity($form->getData());
        }

        throw new InvalidFormException('Invalid submitted data', $form);
    }

    /**
     * Get the parameters for the form from the passed parameters
     *
     * @param FormInterface $form
     * @param array $parameters
     *
     * @return array
     */
    protected function getFormParameters(FormInterface $form, array $parameters)
    {
        $formName = $form->getName();
        if (array_key_exists($formName, $parameters)) {
            return $parameters[$formName];
        }

        return array();
    }

    /**
     * Create entity
     *
     * @return object New instance of entityClass
     */
    public function createEntity()
    {
        return new $this->entityClass;
    }

    /**
     * Save entity
     *
     * @param object $entity
     *
     * @return object entity
     */
    public function saveEntity($entity)
    {
        $this->om->persist($entity);
        $this->om->flush($entity);

        return $entity;
    }

    /**
     * Delete entity
     *
     * @param object $entity
     *
     * @return void
     */
    public function deleteEntity($entity)
    {
        $this->om->remove($entity);
        $this->om->flush($entity);
    }

    /**
     * Get entity repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->om->getRepository($this->entityClass);
    }
}