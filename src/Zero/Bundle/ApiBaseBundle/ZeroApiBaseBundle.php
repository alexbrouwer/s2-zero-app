<?php

namespace Zero\Bundle\ApiBaseBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZeroApiBaseBundle extends Bundle
{
    public function getParent()
    {
        return 'NelmioApiDocBundle';
    }
}
