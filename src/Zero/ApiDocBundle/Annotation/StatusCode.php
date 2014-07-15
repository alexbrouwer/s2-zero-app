<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target("METHOD")
 */
class StatusCode implements AnnotationInterface
{

    /**
     * @var int
     * @Required
     */
    public $code;

    /**
     * @var string
     */
    public $description;
}