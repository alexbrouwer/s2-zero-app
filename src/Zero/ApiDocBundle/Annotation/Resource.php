<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
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