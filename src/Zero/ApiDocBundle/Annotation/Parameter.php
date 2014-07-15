<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Parameter implements AnnotationInterface
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
    public $required = false;

    /**
     * @var string
     */
    public $description;

    /**
     * @return array
     */
    public function getOptions()
    {
        return array(
            'dataType' => $this->dataType,
            'required' => $this->required,
            'description' => $this->description,
        );
    }
} 