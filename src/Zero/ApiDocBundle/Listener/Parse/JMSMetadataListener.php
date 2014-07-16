<?php


namespace Zero\ApiDocBundle\Listener\Parse;

use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Metadata\MetadataFactoryInterface;
use Metadata\PropertyMetadata;
use Zero\ApiDocBundle\DataTypes;
use Zero\ApiDocBundle\Event\ExtractorEvent;
use Zero\ApiDocBundle\Exception\InvalidArgumentException;
use Zero\ApiDocBundle\Util\DocCommentExtractor;

class JMSMetadataListener
{

    private $typeMap = array(
        'integer'  => DataTypes::INTEGER,
        'boolean'  => DataTypes::BOOLEAN,
        'string'   => DataTypes::STRING,
        'float'    => DataTypes::FLOAT,
        'double'   => DataTypes::FLOAT,
        'array'    => DataTypes::COLLECTION,
        'DateTime' => DataTypes::DATETIME,
    );

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MetadataFactoryInterface
     */
    private $factory;

    /**
     * @var PropertyNamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var DocCommentExtractor
     */
    private $commentExtractor;

    /**
     * @param SerializerInterface $serializer
     * @param MetadataFactoryInterface $factory
     * @param PropertyNamingStrategyInterface $namingStrategy
     * @param DocCommentExtractor $commentExtractor
     */
    public function __construct(
        SerializerInterface $serializer,
        MetadataFactoryInterface $factory,
        PropertyNamingStrategyInterface $namingStrategy,
        DocCommentExtractor $commentExtractor
    ) {
        $this->serializer       = $serializer;
        $this->factory          = $factory;
        $this->namingStrategy   = $namingStrategy;
        $this->commentExtractor = $commentExtractor;
    }

    /**
     * @param ExtractorEvent $event
     */
    public function onExtractorParse(ExtractorEvent $event)
    {
        $container = $event->getContainer();

        $parameters = $this->handleInput($container->getInput());
        foreach ($parameters as $name => $options) {
            $container->addParameter($name, $options);
        }

        $response = $this->handleOutput($container->getOutput());
        $container->setResponse($response);
    }

    public function handleInput(array $input)
    {
        if ($this->supports($input)) {
            return $this->getParameters($input);
        }

        return array();
    }

    public function handleOutput(array $output)
    {
        if ($this->supports($output)) {
            return $this->getResponse($output);
        }

        return array();
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function supports(array $options)
    {
        if (is_string($options['class'])) {
            try {
                if ($meta = $this->factory->getMetadataForClass($options['class'])) {
                    return true;
                }
            } catch (\ReflectionException $e) {
            }
        }

        return false;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function getParameters(array $options)
    {
        $className = $options['class'];
        $groups    = $options['groups'];

        return $this->_getParameters($className, array(), $groups);
    }

    public function getResponse(array $options)
    {
        $className = $options['class'];
        $groups    = $options['groups'];

        $data = '{}';
        if(strpos($className, '<') !== -1) {
            $data = '[{}]';
        }

        $response = $this->serializer->deserialize($data, $className, 'json');

        var_dump($className);
        var_dump($response);

        return $response;
    }

    /**
     * @param $className
     * @param array $visited
     * @param array $groups
     *
     * @return array
     */
    protected function _getParameters($className, $visited = array(), array $groups = array())
    {
        $meta = $this->factory->getMetadataForClass($className);

        if (null === $meta) {
            throw new InvalidArgumentException(sprintf('No metadata found for class %s', $className));
        }

        $exclusionStrategies = array();
        if ($groups) {
            $exclusionStrategies[] = new GroupsExclusionStrategy($groups);
        }

        $params            = array();
        $reflection        = new \ReflectionClass($className);
        $defaultProperties = $reflection->getDefaultProperties();

        // Iterate over property metadata
        foreach ($meta->propertyMetadata as $item) {
            if (!is_null($item->type)) {
                $name = $this->namingStrategy->translateName($item);

                $dataType = $this->processDataType($item);

                foreach ($exclusionStrategies as $strategy) {
                    if (true === $strategy->shouldSkipProperty($item, SerializationContext::create())) {
                        continue 2;
                    }
                }

                if (!$dataType['inline']) {
                    $params[$name] = array(
                        'dataType'     => $dataType['normalized'],
                        'actualType'   => $dataType['actualType'],
                        'subType'      => $dataType['class'],
                        'required'     => false,
                        'default'      => isset($defaultProperties[$item->name]) ? $defaultProperties[$item->name] : null,
                        //TODO: can't think of a good way to specify this one, JMS doesn't have a setting for this
                        'description'  => $this->getDescription($item),
                        'readonly'     => $item->readOnly,
                        'sinceVersion' => $item->sinceVersion,
                        'untilVersion' => $item->untilVersion,
                    );

                    if (!is_null($dataType['class']) && false === $dataType['primitive']) {
                        $params[$name]['class'] = $dataType['class'];
                    }
                }

                // we can use type property also for custom handlers, then we don't have here real class name
                if (!class_exists($dataType['class'])) {
                    continue;
                }

                // if class already parsed, continue, to avoid infinite recursion
                if (in_array($dataType['class'], $visited)) {
                    continue;
                }

                // check for nested classes with JMS metadata
                if ($dataType['class'] && false === $dataType['primitive'] && null !== $this->factory->getMetadataForClass($dataType['class'])) {
                    $visited[] = $dataType['class'];
                    $children  = $this->_getParameters($dataType['class'], $visited, $groups);

                    if ($dataType['inline']) {
                        $params = array_merge($params, $children);
                    } else {
                        $params[$name]['children'] = $children;
                    }
                }
            }
        }

        return $params;
    }

    /**
     * @param PropertyMetadata $item
     *
     * @return string
     */
    protected function getDescription(PropertyMetadata $item)
    {
        $ref = new \ReflectionClass($item->class);
        if ($item instanceof VirtualPropertyMetadata) {
            $extracted = $this->commentExtractor->getDocCommentText($ref->getMethod($item->getter));
        } else {
            $extracted = $this->commentExtractor->getDocCommentText($ref->getProperty($item->name));
        }

        return $extracted;
    }

    /**
     * Check the various ways JMS describes values in arrays, and
     * get the value type in the array
     *
     * @param PropertyMetadata $item
     *
     * @return string|null
     */
    protected function getNestedTypeInArray(PropertyMetadata $item)
    {
        if (isset($item->type['name']) && in_array($item->type['name'], array('array', 'ArrayCollection'))) {
            if (isset($item->type['params'][1]['name'])) {
                // E.g. array<string, MyNamespaceMyObject>
                return $item->type['params'][1]['name'];
            }
            if (isset($item->type['params'][0]['name'])) {
                // E.g. array<MyNamespaceMyObject>
                return $item->type['params'][0]['name'];
            }
        }

        return null;
    }

    /**
     * Figure out a normalized data type (for documentation), and get a
     * nested class name, if available.
     *
     * @param PropertyMetadata $item
     *
     * @return array
     */
    protected function processDataType(PropertyMetadata $item)
    {
        // check for a type inside something that could be treated as an array
        if ($nestedType = $this->getNestedTypeInArray($item)) {
            if ($this->isPrimitive($nestedType)) {
                return array(
                    'normalized' => sprintf("array of %ss", $nestedType),
                    'actualType' => DataTypes::COLLECTION,
                    'class'      => $this->typeMap[$nestedType],
                    'primitive'  => true,
                    'inline'     => false,
                );
            }

            $exp = explode("\\", $nestedType);

            return array(
                'normalized' => sprintf("array of objects (%s)", end($exp)),
                'actualType' => DataTypes::COLLECTION,
                'class'      => $nestedType,
                'primitive'  => false,
                'inline'     => false,
            );
        }

        $type = $item->type['name'];

        // could be basic type
        if ($this->isPrimitive($type)) {
            return array(
                'normalized' => $type,
                'actualType' => $this->typeMap[$type],
                'class'      => null,
                'primitive'  => true,
                'inline'     => false,
            );
        }

        // we can use type property also for custom handlers, then we don't have here real class name
        if (!class_exists($type)) {
            return array(
                'normalized' => sprintf("custom handler result for (%s)", $type),
                'class'      => $type,
                'actualType' => DataTypes::MODEL,
                'primitive'  => false,
                'inline'     => false,
            );
        }

        // if we got this far, it's a general class name
        $exp = explode("\\", $type);

        return array(
            'normalized' => sprintf("object (%s)", end($exp)),
            'class'      => $type,
            'actualType' => DataTypes::MODEL,
            'primitive'  => false,
            'inline'     => $item->inline,
        );
    }

    protected function isPrimitive($type)
    {
        return in_array($type, array('boolean', 'integer', 'string', 'float', 'double', 'array', 'DateTime'));
    }
}