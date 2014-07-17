<?php

namespace Zero\Bundle\ApiSecurityBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;

/**
 * AccessToken
 */
class AccessToken extends BaseAccessToken
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
