<?php


namespace Zero\UserBundle\Tests\Entity;

use Zero\UserBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testUsername()
    {
        $expected = 'test-user';

        $user = new User();
        $user->setUsername($expected);
        $this->assertEquals($expected, $user->getUsername());
    }

    public function testDisplayName()
    {
        $expected = 'test-user';

        $user = new User();
        $user->setDisplayName($expected);
        $this->assertEquals($expected, $user->getDisplayName());
    }

    public function testEmail()
    {
        $expected = 'me@example.com';

        $user = new User();
        $user->setEmail($expected);
        $this->assertEquals($expected, $user->getEmail());
    }

    public function testGroups() {

        $expected = \Mockery::mock('Zero\UserBundle\Entity\User\Group');

        $user = new User();
        $this->assertCount(0, $user->getGroups());

        $user->addGroup($expected);
        $this->assertCount(1, $user->getGroups());
        $this->assertContains($expected, $user->getGroups());

        $user->removeGroup($expected);
        $this->assertNotContains($expected, $user->getGroups());
        $this->assertCount(0, $user->getGroups());
    }
}
 