<?php


namespace Zero\Bundle\UserBundle\Service\User;

use Zero\Bundle\UserBundle\Entity\User\Group;
use Zero\Bundle\ApiBaseBundle\Manager\EntityManager;

class GroupManager extends EntityManager
{
    /**
     * @param array $parameters
     *
     * @return Group
     */
    public function create(array $parameters)
    {
        $group = $this->createEntity();

        return $this->processForm($group, $parameters, self::METHOD_POST);
    }

    /**
     * @param Group $group
     * @param array $parameters
     *
     * @return Group
     */
    public function update(Group $group, array $parameters)
    {
        return $this->processForm($group, $parameters, self::METHOD_PUT);
    }

    /**
     * @param Group $group
     * @param array $parameters
     *
     * @return Group
     */
    public function patch(Group $group, array $parameters)
    {
        return $this->processForm($group, $parameters, self::METHOD_PATCH);
    }

    /**
     * @param int $id
     *
     * @return Group|null
     */
    public function get($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param Group $group
     *
     * @return void
     */
    public function delete(Group $group)
    {
        $this->deleteEntity($group);
    }

    /**
     * @return Group[]
     */
    public function findBy()
    {
        return $this->getRepository()->findAll();
    }
} 
