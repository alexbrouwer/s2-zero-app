<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Requirement implements AnnotationInterface
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
    public $requirement;

    /**
     * @var string
     */
    public $description;

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'dataType' => $this->dataType,
            'requirement' => $this->requirement,
            'description' => $this->description
        );
    }
} 