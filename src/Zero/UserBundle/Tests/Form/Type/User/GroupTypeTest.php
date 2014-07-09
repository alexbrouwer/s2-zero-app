<?php


namespace Zero\UserBundle\Tests\Form\Type\User;


use Symfony\Component\Form\Test\TypeTestCase;
use Zero\UserBundle\Entity\User\Group;
use Zero\UserBundle\Form\Type\User\GroupType;

class GroupTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'name'    => 'name',
            'roles'       => array('testRole')
        );

        $object = new Group();
        $object->setName($formData['name']);
        $object->setRoles($formData['roles']);

        $type = new GroupType();
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
 