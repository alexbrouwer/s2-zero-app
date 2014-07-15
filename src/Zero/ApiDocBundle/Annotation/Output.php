<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Output implements AnnotationInterface
{

    /**
     * @var string
     * @Required()
     */
    public $class;

    /**
     * @var string[]
     */
    public $groups = array();

}