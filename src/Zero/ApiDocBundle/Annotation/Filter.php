<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Filter class
 *
 * @package Zero\ApiDocBundle\Annotation
 *
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Filter
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
    public function toArray()
    {
        $arr = array(
            'dataType' => $this->dataType
        );

        if ($this->pattern) {
            $arr['pattern'] = $this->pattern;
        }

        return $arr;
    }
} 