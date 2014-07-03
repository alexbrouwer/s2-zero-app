<?php


namespace Zero\UserBundle\Service\User;

use Zero\UserBundle\Entity\User\Group;
use Zero\ApiBaseBundle\Manager\AbstractManager;

class GroupManager extends AbstractManager
{
    /**
     * @param array $parameters
     *
     * @return Group
     */
    public function create(array $parameters)
    {
        $Group = $this->createEntity();

        return $this->processForm($Group, $parameters, self::METHOD_POST);
    }

    /**
     * @param Group $Group
     * @param array $parameters
     *
     * @return Group
     */
    public function update(Group $Group, array $parameters)
    {
        return $this->processForm($Group, $parameters, self::METHOD_PUT);
    }

    /**
     * @param Group $Group
     * @param array $parameters
     *
     * @return Group
     */
    public function patch(Group $Group, array $parameters)
    {
        return $this->processForm($Group, $parameters, self::METHOD_PATCH);
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
     * @param Group $Group
     *
     * @return void
     */
    public function delete(Group $Group)
    {
        $this->deleteEntity($Group);
    }

    /**
     * @return Group[]
     */
    public function findBy()
    {
        return $this->getRepository()->findAll();
    }
} 