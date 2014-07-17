<?php

namespace Zero\Bundle\ApiSecurityBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;

/**
 * Client
 */
class Client extends BaseClient
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
