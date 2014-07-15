<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Section implements AnnotationInterface
{

    /**
     * @var string
     * @Required()
     */
    public $value;

}