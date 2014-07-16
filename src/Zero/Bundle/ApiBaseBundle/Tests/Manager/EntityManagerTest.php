<?php


namespace Zero\Bundle\ApiBaseBundle\Tests\Manager;

use Zero\Bundle\ApiBaseBundle\Exception\InvalidFormException;
use Zero\Bundle\ApiBaseBundle\Manager\EntityManager;

class EntityManagerTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_CLASS = 'Zero\Bundle\ApiBaseBundle\Tests\Manager\EntityManagerTestEntity';
    const FORM_CLASS = 'Zero\Bundle\ApiBaseBundle\Tests\Manager\EntityManagerTestFormType';

    public function testInstantiate()
    {
        $entityManager = new EntityManager(self::ENTITY_CLASS, 'foo');
        $this->assertEquals(self::ENTITY_CLASS, $entityManager->getEntityClass());
    }

    public function testCreateEntity()
    {
        $entityManager = new EntityManager(self::ENTITY_CLASS, 'foo');
        $this->assertInstanceOf(self::ENTITY_CLASS, $entityManager->createEntity());
    }

    public function testCreateFormWithService()
    {
        $expected = 'entity-manager-form';

        $entityManager = new EntityManager(self::ENTITY_CLASS, $expected);
        $entity        = $entityManager->createEntity();

        $formFactory = \Mockery::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('createNamed')->with($expected, 'form', $entity, array('method' => EntityManager::METHOD_PUT));
        $entityManager->setFormFactory($formFactory);

        $entityManager->createForm($entity);
    }

    public function testCreateFormWithClass()
    {
        $expected = self::FORM_CLASS;

        $entityManager = new EntityManager(self::ENTITY_CLASS, $expected);
        $entity        = $entityManager->createEntity();

        $formFactory = \Mockery::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')->with($expected, $entity, array('method' => EntityManager::METHOD_PUT));
        $entityManager->setFormFactory($formFactory);

        $entityManager->createForm($entity);
    }

    public function testCreateFormWithInstance()
    {
        $formClass = self::FORM_CLASS;
        $expected  = new $formClass;

        $entityManager = new EntityManager(self::ENTITY_CLASS, $expected);
        $entity        = $entityManager->createEntity();

        $formFactory = \Mockery::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')->with($expected, $entity, array('method' => EntityManager::METHOD_PUT));
        $entityManager->setFormFactory($formFactory);

        $entityManager->createForm($entity);
    }

    public function testProcessForm()
    {
        $formName = 'test-form';
        $formData = array(
            'test-form' => array(
                'foo' => 'bar'
            ),
            'not-in-form' => array(
                'baz' => 'foobar'
            )
        );

        $entityManager = new EntityManager(self::ENTITY_CLASS, self::FORM_CLASS);
        $entity = $entityManager->createEntity();

        $form = \Mockery::mock('Symfony\Component\Form\FormInterface');
        $form->shouldReceive('getName')->andReturn($formName);
        $form->shouldReceive('submit')->with($formData[$formName], true);
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('getData')->andReturn($entity);

        $formFactory = \Mockery::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')->andReturn($form);
        $entityManager->setFormFactory($formFactory);

        $objectManager = \Mockery::mock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->shouldReceive('persist')->with($entity);
        $objectManager->shouldReceive('flush')->with($entity);
        $entityManager->setObjectManager($objectManager);

        $entityManager->processForm($entity, $formData);
    }

    /**
     * @expectedException \Zero\Bundle\ApiBaseBundle\Exception\InvalidFormException
     */
    public function testProcessFormShouldThrowInvalidFormExceptionOnIncompleteData()
    {
        $formName = 'test-form';
        $formData = array();

        $entityManager = new EntityManager(self::ENTITY_CLASS, self::FORM_CLASS);
        $entity = $entityManager->createEntity();

        $form = \Mockery::mock('Symfony\Component\Form\FormInterface');
        $form->shouldReceive('getName')->andReturn($formName);
        $form->shouldReceive('submit')->with(array(), true);
        $form->shouldReceive('isValid')->andReturn(false);
        $form->shouldReceive('getData')->never();

        $formFactory = \Mockery::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')->andReturn($form);
        $entityManager->setFormFactory($formFactory);

        $entityManager->processForm($entity, $formData);
    }

    public function testProcessFormShouldNotThrowInvalidFormExceptionOnIncompleteDataWithPatch()
    {
        $formName = 'test-form';
        $formData = array();

        $entityManager = new EntityManager(self::ENTITY_CLASS, self::FORM_CLASS);
        $entity = $entityManager->createEntity();

        $form = \Mockery::mock('Symfony\Component\Form\FormInterface');
        $form->shouldReceive('getName')->andReturn($formName);
        $form->shouldReceive('submit')->with(array(), false);
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($entity);

        $formFactory = \Mockery::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')->andReturn($form);
        $entityManager->setFormFactory($formFactory);

        $objectManager = \Mockery::mock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->shouldReceive('persist')->with($entity);
        $objectManager->shouldReceive('flush')->with($entity);
        $entityManager->setObjectManager($objectManager);

        try {
            $entityManager->processForm($entity, $formData, EntityManager::METHOD_PATCH);
        } catch(InvalidFormException $e) {
            $this->fail('Should not throw a InvalidFormException');
        }
    }

    public function testSaveEntity()
    {
        $entityManager = new EntityManager(self::ENTITY_CLASS, 'foo');
        $entity        = $entityManager->createEntity();

        $objectManager = \Mockery::mock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->shouldReceive('persist')->with($entity);
        $objectManager->shouldReceive('flush')->with($entity);
        $entityManager->setObjectManager($objectManager);

        $entityManager->saveEntity($entity);
    }

    public function testDeleteEntity()
    {
        $entityManager = new EntityManager(self::ENTITY_CLASS, 'foo');
        $entity        = $entityManager->createEntity();

        $objectManager = \Mockery::mock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->shouldReceive('remove')->with($entity);
        $objectManager->shouldReceive('flush')->with($entity);
        $entityManager->setObjectManager($objectManager);

        $entityManager->deleteEntity($entity);
    }

    public function testGetRepository()
    {
        $entityManager = new EntityManager(self::ENTITY_CLASS, 'foo');

        $objectManager = \Mockery::mock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->shouldReceive('getRepository')->with(self::ENTITY_CLASS);
        $entityManager->setObjectManager($objectManager);

        $entityManager->getRepository();
    }
}
 