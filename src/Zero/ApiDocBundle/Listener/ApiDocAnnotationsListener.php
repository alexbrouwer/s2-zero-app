<?php


namespace Zero\ApiDocBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Zero\ApiDocBundle\Annotation;
use Zero\ApiDocBundle\Event\ExtractorEvent;
use Zero\ApiDocBundle\Exception\UnknownAnnotationException;
use Zero\ApiDocBundle\RestDoc;

class ApiDocAnnotationsListener
{
    /**
     * Handle
     *
     * @param ExtractorEvent $event
     */
    public function onExtractorHandle(ExtractorEvent $event)
    {
        $container   = $event->getContainer();
        $annotations = $event->getAnnotations();

        foreach ($annotations as $annotation) {
            if (!$this->supportsAnnotation($annotation)) {
                continue;
            }

            $annotationClass      = get_class($annotation);
            $annotationNamespaces = explode('\\', $annotationClass);
            $annotationName       = array_pop($annotationNamespaces);

            $handlerMethod = 'handle' . $annotationName . 'Annotation';
            if (method_exists($this, $handlerMethod)) {
                $this->$handlerMethod($container, $annotation);
            } else {
                throw new UnknownAnnotationException(sprintf('Cannot handle annotation "%s"', get_class($annotation)));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function supportsAnnotation($annotation)
    {
        return $annotation instanceof Annotation\AnnotationInterface;
    }

    protected function handleDeprecatedAnnotation(RestDoc $container, Annotation\Deprecated $annotation)
    {
        $container->setDeprecated($annotation->value);
    }

    protected function handleDescriptionAnnotation(RestDoc $container, Annotation\Description $annotation)
    {
        $container->setDescription($annotation->value);
    }

    protected function handleFilterAnnotation(RestDoc $container, Annotation\Filter $annotation)
    {
        $container->addFilter($annotation->name, $annotation->getOptions());
    }

    protected function handleHttpsAnnotation(RestDoc $container, Annotation\Https $annotation)
    {
        $container->setHttps($annotation->value);
    }

    protected function handleInputAnnotation(RestDoc $container, Annotation\Input $annotation)
    {
        $container->setInput($annotation->class, $annotation->groups);
    }

    protected function handleOutputAnnotation(RestDoc $container, Annotation\Output $annotation)
    {
        $container->setOutput($annotation->class, $annotation->groups);
    }

    protected function handleParameterAnnotation(RestDoc $container, Annotation\Parameter $annotation)
    {
        $container->addParameter($annotation->name, $annotation->getOptions());
    }

    protected function handleRequirementAnnotation(RestDoc $container, Annotation\Requirement $annotation)
    {
        $container->addRequirement($annotation->name, $annotation->getOptions());
    }

    /**
     * Handle resource
     *
     * @param RestDoc $container
     * @param Annotation\Resource $annotation
     */
    protected function handleResourceAnnotation(RestDoc $container, Annotation\Resource $annotation)
    {
        $container->setResource($annotation->value);
    }

    /**
     * Handle section
     *
     * @param RestDoc $container
     * @param Annotation\Section $annotation
     */
    protected function handleSectionAnnotation(RestDoc $container, Annotation\Section $annotation)
    {
        $container->setSection($annotation->value);
    }

    /**
     * Handle status code
     *
     * @param RestDoc $container
     * @param Annotation\StatusCode $annotation
     */
    protected function handleStatusCodeAnnotation(RestDoc $container, Annotation\StatusCode $annotation)
    {
        $code        = $annotation->code;
        $description = $annotation->description;
        if (!$description && array_key_exists((int)$code, Response::$statusTexts)) {
            $description = Response::$statusTexts[$code];
        }

        $container->addStatusCode($code, $description);
    }

    /**
     * Handle tag
     *
     * @param RestDoc $container
     * @param Annotation\Tag $annotation
     */
    protected function handleTagAnnotation(RestDoc $container, Annotation\Tag $annotation)
    {
        $container->addTag($annotation->value);
    }
}