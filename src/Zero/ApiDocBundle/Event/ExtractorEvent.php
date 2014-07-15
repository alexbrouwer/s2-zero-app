<?php


namespace Zero\ApiDocBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Route;
use Zero\ApiDocBundle\RestDoc;

class ExtractorEvent extends Event
{

    /**
     * @var RestDoc
     */
    private $container;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var \ReflectionMethod
     */
    private $method;

    /**
     * @var array
     */
    private $annotations = array();

    /**
     * @param RestDoc $container
     * @param \ReflectionMethod $method
     * @param Route $route
     * @param array $annotations
     */
    public function __construct(RestDoc $container, \ReflectionMethod $method, Route $route, array $annotations)
    {
        $this->container   = $container;
        $this->route       = $route;
        $this->method      = $method;
        $this->annotations = $annotations;
    }

    /**
     * Get Annotations
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Get Container
     *
     * @return RestDoc
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set container
     *
     * @param RestDoc $container
     *
     * @return ExtractorEvent
     */
    public function setContainer(RestDoc $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get Method
     *
     * @return \ReflectionMethod
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get Route
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }
}