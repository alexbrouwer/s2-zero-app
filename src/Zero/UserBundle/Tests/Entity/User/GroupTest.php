<?php


namespace Zero\UserBundle\Tests\Entity\User;

use Zero\UserBundle\Entity\User\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $expected = 'test-group';
        $group    = new Group();

        $group->setName($expected);
        $this->assertEquals($expected, $group->getName());
    }

    public function testRoles()
    {
        $expected = array('ROLE_1');
        $group    = new Group();

        $group->setRoles($expected);
        $this->assertEquals($expected, $group->getRoles());
    }
}
 