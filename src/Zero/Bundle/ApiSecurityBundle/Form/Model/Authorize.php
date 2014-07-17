<?php


namespace Zero\Bundle\ApiSecurityBundle\Form\Model;


class Authorize
{
    /**
     * @var bool
     */
    protected $allowAccess;

    /**
     * Get allow access
     *
     * @return bool
     */
    public function getAllowAccess()
    {
        return $this->allowAccess;
    }

    /**
     * Set allow access
     *
     * @param bool $allowAccess
     */
    public function setAllowAccess($allowAccess)
    {
        $this->allowAccess = (bool) $allowAccess;
    }
} 