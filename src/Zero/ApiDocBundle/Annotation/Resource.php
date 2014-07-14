<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Resource class
 *
 * @package Zero\ApiDocBundle\Annotation
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class Resource implements AnnotationInterface
{

    /**
     * @var bool
     */
    public $value = true;
}