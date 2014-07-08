<?php


namespace Zero\UserBundle\Tests\Form;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Zero\UserBundle\Entity\User;
use Zero\UserBundle\Form\UserType;

class UserTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $mockEntityManager->shouldReceive('contains')->andReturn(true);
        $mockEntityManager->shouldReceive('initializeObject')->andReturn(true);

        $repositories = $this->getRepositories();
        foreach ($repositories as $class => $repository) {
            $mockClassMetadata = \Mockery::mock('\Doctrine\ORM\Mapping\ClassMetadata');
            $mockClassMetadata->shouldReceive('getName')->andReturn($class);
            $mockClassMetadata->shouldReceive('getIdentifierFieldNames')->andReturn(array('id'));
            $mockClassMetadata->shouldReceive('getTypeOfField')->with('id')->andReturn('integer');
            $mockClassMetadata->shouldReceive('getIdentifierValues')->andReturn(array());

            $mockEntityManager->shouldReceive('getClassMetadata')->andReturn($mockClassMetadata);
            $mockEntityManager->shouldReceive('getRepository')->with($class)->andReturn($repository);
        }

        $mockRegistry = \Mockery::mock('\Doctrine\Bundle\DoctrineBundle\Registry');
        $mockRegistry->shouldReceive('getManagerForClass')->andReturn($mockEntityManager);

        $mockEntityType = \Mockery::mock('Symfony\Bridge\Doctrine\Form\Type\EntityType', array($mockRegistry));
        $mockEntityType->shouldReceive('getName')->andReturn('entity');
        $mockEntityType->shouldDeferMissing();

        return array(
            new PreloadedExtension(
                array(
                    $mockEntityType->getName() => $mockEntityType,
                ), array()
            )
        );
    }

    protected function getRepositories()
    {
        $group = \Mockery::mock('Zero\UserBundle\Entity\User\Group');
        $group->shouldReceive('getId')->andReturn(1);

        $userGroupRep = \Mockery::mock('Doctrine\ORM\EntityRepository');
        $userGroupRep->shouldReceive('findAll')->andReturn(array($group));

        return array(
            'Zero\UserBundle\Entity\User\Group' => $userGroupRep
        );
    }

    public function testSubmitValidData()
    {
        $formData = array(
            'username'    => 'username',
            'email'       => 'email',
            'displayName' => 'displayName',
            'groups'      => array(1)
        );

        $object = new User();
        $object->setUsername($formData['username']);
        $object->setEmail($formData['email']);
        $object->setDisplayName($formData['displayName']);

        $type = new UserType();
        $form = $this->factory->create($type);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
 