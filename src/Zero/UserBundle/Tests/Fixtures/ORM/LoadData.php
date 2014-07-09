<?php


namespace Zero\UserBundle\Tests\Fixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zero\UserBundle\Entity\User;

class LoadData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $group = $this->createGroup('group1', array('ROLE1', 'ROLE2'));
        $manager->persist($group);

        $user = new User();
        $user->setUsername('username1');
        $user->setEmail('username1@example.com');
        $user->setDisplayName('User 1');
        $user->addGroup($group);
        $manager->persist($user);

        $group = $this->createGroup('group2', array('ROLE2', 'ROLE3'));
        $manager->persist($group);

        $user = new User();
        $user->setUsername('username2');
        $user->setEmail('username2@example.com');
        $user->setDisplayName('User 2');
        $user->addGroup($group);
        $manager->persist($user);

        $manager->flush();
    }

    private function createGroup($name, array $roles) {
        $group = new User\Group();
        $group->setName($name);
        $group->setRoles($roles);

        return $group;
    }
} 