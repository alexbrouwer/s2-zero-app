<?php


namespace Zero\UserBundle\Tests\Fixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zero\UserBundle\Entity\User;

class LoadData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $group1 = new User\Group();
        $group1->setName('group1');
        $group1->setRoles(array('ROLE1', 'ROLE2'));
        $manager->persist($group1);

        $group2 = new User\Group();
        $group2->setName('group2');
        $group2->setRoles(array('ROLE3', 'ROLE2'));
        $manager->persist($group2);

        $user = new User();
        $user->setUsername('username1');
        $user->setEmail('username1@example.com');
        $user->setDisplayName('User 1');
        $user->addGroup($group1);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('username2');
        $user->setEmail('username2@example.com');
        $user->setDisplayName('User 2');
        $user->addGroup($group2);
        $manager->persist($user);

        $manager->flush();
    }
} 