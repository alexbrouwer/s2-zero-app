<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Filter implements AnnotationInterface
{

    /**
     * @var string
     * @Required
     */
    public $name;

    /**
     * @var string
     */
    public $dataType = 'string';

    /**
     * @var string
     */
    public $pattern;

    /**
     * @return array
     */
    public function getOptions()
    {
        return array(
            'dataType' => $this->dataType,
            'pattern' => $this->pattern,
        );
    }
} 