<?php


namespace Zero\ApiDocBundle\Annotation;

/**
 * Filters class
 *
 * @package Zero\ApiDocBundle\Annotation
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class Filters implements AnnotationInterface
{

    /**
     * @var \Zero\ApiDocBundle\Annotation\Filter[]
     */
    public $filters = array();
} 