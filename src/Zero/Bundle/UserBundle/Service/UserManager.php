<?php


namespace Zero\Bundle\UserBundle\Service;

use Zero\Bundle\UserBundle\Entity\User;
use Zero\Bundle\ApiBaseBundle\Manager\EntityManager;

class UserManager extends EntityManager
{
    /**
     * @param array $parameters
     *
     * @return User
     */
    public function create(array $parameters)
    {
        $user = $this->createEntity();

        return $this->processForm($user, $parameters, self::METHOD_POST);
    }

    /**
     * @param User $user
     * @param array $parameters
     *
     * @return User
     */
    public function update(User $user, array $parameters)
    {
        return $this->processForm($user, $parameters, self::METHOD_PUT);
    }

    /**
     * @param User $user
     * @param array $parameters
     *
     * @return User
     */
    public function patch(User $user, array $parameters)
    {
        return $this->processForm($user, $parameters, self::METHOD_PATCH);
    }

    /**
     * @param int $id
     *
     * @return User|null
     */
    public function get($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param User $user
     *
     * @return void
     */
    public function delete(User $user)
    {
        $this->deleteEntity($user);
    }

    /**
     * @return User[]
     */
    public function findBy()
    {
        return $this->getRepository()->findAll();
    }
} 