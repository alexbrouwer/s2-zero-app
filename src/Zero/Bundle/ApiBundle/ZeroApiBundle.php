<?php

namespace Zero\Bundle\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZeroApiBundle extends Bundle
{
    public function getParent()
    {
        return 'NelmioApiDocBundle';
    }
}
