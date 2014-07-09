<?php


namespace Zero\UserBundle\Tests\Fixtures\Doctrine;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zero\UserBundle\Entity\User\Group;

class UserGroupData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $group = new Group();
        $group->setName('group1');
        $group->setRoles(array('ROLE1', 'ROLE2'));

        $manager->persist($group);

        $group = new Group();
        $group->setName('group2');
        $group->setRoles(array('ROLE3', 'ROLE2'));

        $manager->persist($group);

        $manager->flush();
    }
} 