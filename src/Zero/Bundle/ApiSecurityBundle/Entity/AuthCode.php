<?php

namespace Zero\Bundle\ApiSecurityBundle\Entity;



use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;

/**
 * AuthCode
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @var integer
     */
    protected $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
