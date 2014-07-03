<?php


namespace Zero\UserBundle\Service;

use Zero\UserBundle\Model\UserInterface;
use Zero\ApiBaseBundle\Manager\AbstractManager;

class UserManager extends AbstractManager
{
    /**
     * @param array $parameters
     *
     * @return UserInterface
     */
    public function create(array $parameters)
    {
        $user = $this->createEntity();

        return $this->processForm($user, $parameters, self::METHOD_POST);
    }

    /**
     * @param UserInterface $user
     * @param array $parameters
     *
     * @return UserInterface
     */
    public function update(UserInterface $user, array $parameters)
    {
        return $this->processForm($user, $parameters, self::METHOD_PUT);
    }

    /**
     * @param UserInterface $user
     * @param array $parameters
     *
     * @return UserInterface
     */
    public function patch(UserInterface $user, array $parameters)
    {
        return $this->processForm($user, $parameters, self::METHOD_PATCH);
    }

    /**
     * @param string $userName
     *
     * @return UserInterface|null
     */
    public function get($userName)
    {
        return $this->getRepository()->findOneBy(array('username' => $userName));
    }

    /**
     * @param UserInterface $user
     *
     * @return void
     */
    public function delete(UserInterface $user)
    {
        $this->deleteEntity($user);
    }

    /**
     * @return UserInterface[]
     */
    public function findBy()
    {
        return $this->getRepository()->findAll();
    }
} 