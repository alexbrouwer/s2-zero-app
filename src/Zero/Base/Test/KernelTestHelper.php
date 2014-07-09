<?php


namespace Zero\Base\Test;

class KernelTestHelper
{
    /**
     * @return \AppKernel
     */
    public static function createTestKernel()
    {
        return new \AppKernel('test', true);
    }
} 