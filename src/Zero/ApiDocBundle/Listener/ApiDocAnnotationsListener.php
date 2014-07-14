<?php


namespace Zero\ApiDocBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Zero\ApiDocBundle\Annotation;
use Zero\ApiDocBundle\Event\ExtractorEvent;
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
        $container = $event->getContainer();
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

    protected function handleFiltersAnnotation(RestDoc $container, Annotation\Filters $filters)
    {
        foreach ($filters->filters as $filter) {
            $container->addFilter($filter->name, $filter->toArray());
        }
    }

    protected function handleDescriptionAnnotation(RestDoc $container, Annotation\Description $description)
    {
        $container->setDescription($description->value);
    }

    protected function handleResourceAnnotation(RestDoc $container, Annotation\Resource $resource)
    {
        $container->setResource($resource->value);
    }

    protected function handleStatusCodeAnnotation(RestDoc $container, Annotation\StatusCode $statusCode)
    {
        $code        = $statusCode->code;
        $description = $statusCode->description;
        if (!$description && array_key_exists((int)$code, Response::$statusTexts)) {
            $description = Response::$statusTexts[$code];
        }

        $container->addStatusCode($code, $description);
    }
}