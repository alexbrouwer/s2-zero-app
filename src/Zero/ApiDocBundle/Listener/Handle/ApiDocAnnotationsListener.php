<?php


namespace Zero\ApiDocBundle\Listener\Handle;

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

    /**
     * Handle api annotation
     *
     * @param RestDoc $container
     * @param Annotation\Api $annotation
     */
    protected function handleApiAnnotation(RestDoc $container, Annotation\Api $annotation)
    {
        // Do nothing
    }

    /**
     * Handle deprecated annotation
     *
     * @param RestDoc $container
     * @param Annotation\Deprecated $annotation
     */
    protected function handleDeprecatedAnnotation(RestDoc $container, Annotation\Deprecated $annotation)
    {
        $container->setDeprecated($annotation->value);
    }

    /**
     * Handle description annotation
     *
     * @param RestDoc $container
     * @param Annotation\Description $annotation
     */
    protected function handleDescriptionAnnotation(RestDoc $container, Annotation\Description $annotation)
    {
        $container->setDescription($annotation->value);
    }

    /**
     * Handle filter annotation
     *
     * @param RestDoc $container
     * @param Annotation\Filter $annotation
     */
    protected function handleFilterAnnotation(RestDoc $container, Annotation\Filter $annotation)
    {
        $container->addFilter($annotation->name, $annotation->getOptions());
    }

    /**
     * Handle https annotation
     *
     * @param RestDoc $container
     * @param Annotation\Https $annotation
     */
    protected function handleHttpsAnnotation(RestDoc $container, Annotation\Https $annotation)
    {
        $container->setHttps($annotation->value);
    }

    /**
     * Handle input annotation
     *
     * @param RestDoc $container
     * @param Annotation\Input $annotation
     */
    protected function handleInputAnnotation(RestDoc $container, Annotation\Input $annotation)
    {
        $container->setInput($annotation->class, $annotation->groups);
    }

    /**
     * Handle output annotation
     *
     * @param RestDoc $container
     * @param Annotation\Output $annotation
     */
    protected function handleOutputAnnotation(RestDoc $container, Annotation\Output $annotation)
    {
        $container->setOutput($annotation->class, $annotation->groups);
    }

    /**
     * Handle parameter annotation
     *
     * @param RestDoc $container
     * @param Annotation\Parameter $annotation
     */
    protected function handleParameterAnnotation(RestDoc $container, Annotation\Parameter $annotation)
    {
        $container->addParameter($annotation->name, $annotation->getOptions());
    }

    /**
     * Handle requirement annotation
     *
     * @param RestDoc $container
     * @param Annotation\Requirement $annotation
     */
    protected function handleRequirementAnnotation(RestDoc $container, Annotation\Requirement $annotation)
    {
        $container->addRequirement($annotation->name, $annotation->getOptions());
    }

    /**
     * Handle resource annotation
     *
     * @param RestDoc $container
     * @param Annotation\Resource $annotation
     */
    protected function handleResourceAnnotation(RestDoc $container, Annotation\Resource $annotation)
    {
        $container->setResource($annotation->value);
    }

    /**
     * Handle section annotation
     *
     * @param RestDoc $container
     * @param Annotation\Section $annotation
     */
    protected function handleSectionAnnotation(RestDoc $container, Annotation\Section $annotation)
    {
        $container->setSection($annotation->value);
    }

    /**
     * Handle status code annotation
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
     * Handle tag annotation
     *
     * @param RestDoc $container
     * @param Annotation\Tag $annotation
     */
    protected function handleTagAnnotation(RestDoc $container, Annotation\Tag $annotation)
    {
        $container->addTag($annotation->value);
    }
}