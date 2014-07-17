<?php

namespace Zero\Bundle\ApiSecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZeroApiSecurityBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSOAuthServerBundle';
    }
}
