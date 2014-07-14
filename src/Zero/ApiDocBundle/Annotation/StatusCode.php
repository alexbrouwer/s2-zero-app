<?php


namespace Zero\ApiDocBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Zero\ApiDocBundle\Exception\InvalidArgumentException;

/**
 * StatusCode class
 *
 * @package Zero\ApiDocBundle\Annotation
 *
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

//    /**
//     * @param array $values
//     */
//    public function __construct(array $values)
//    {
//        if (array_key_exists('value', $values)) {
//            $this->code = $values['value'];
//            unset($values['value']);
//        }
//
//        foreach ($values as $key => $value) {
//            if (!property_exists($this, $key)) {
//                throw new InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
//            }
//
//            $this->$key = $value;
//        }
//    }
} 