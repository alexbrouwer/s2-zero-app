<?php

namespace Zero\ApiDocBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZeroApiDocBundle extends Bundle
{
    public function getParent()
    {
        return 'NelmioApiDocBundle';
    }
}
