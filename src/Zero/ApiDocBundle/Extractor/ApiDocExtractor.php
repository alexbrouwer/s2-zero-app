<?php


namespace Zero\ApiDocBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Zero\ApiDocBundle\Event\ExtractorEvent;
use Zero\ApiDocBundle\Exception;
use Zero\ApiDocBundle\Extractor\Handler\HandlerInterface;
use Zero\ApiDocBundle\ExtractorEvents;
use Zero\ApiDocBundle\RestDoc;

class ApiDocExtractor
{
    const ANNOTATION_CLASS = 'Zero\ApiDocBundle\Annotation\Api';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param ContainerInterface $container
     * @param RouterInterface $router
     * @param Reader $reader
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ContainerInterface $container,
        RouterInterface $router,
        Reader $reader,
        EventDispatcherInterface $dispatcher
    ) {
        $this->container  = $container;
        $this->router     = $router;
        $this->reader     = $reader;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get routes
     *
     * @return \Symfony\Component\Routing\Route[]
     */
    public function getRoutes()
    {
        return $this->router->getRouteCollection()->all();
    }

    /**
     * Get all annotations
     *
     * @return RestDoc[]
     */
    public function all()
    {
        return $this->extractDocs($this->getRoutes());
    }

    /**
     * Extract docs for routes
     *
     * @param array $routes
     *
     * @return RestDoc[]
     */
    public function extractDocs(array $routes)
    {
        $routeDocs = array();
        foreach ($routes as $route) {
            if (!$route instanceof Route) {
                throw new Exception\InvalidArgumentException(
                    sprintf('All elements of $routes must be instances of Route. "%s" given.', gettype($route))
                );
            }

            if ($method = $this->getReflectionMethod($route->getDefault('_controller'))) {
                if (!is_null($this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS))) {
                    $annotations = $this->reader->getMethodAnnotations($method);
                    $routeDoc    = $this->getRouteDoc($method, $route, $annotations);
                    if ($routeDoc) {
                        $routeDocs[] = $routeDoc;
                    }
                }
            }
        }

        return $routeDocs;
    }

    /**
     * Get doc for route
     *
     * @param \ReflectionMethod $method
     * @param Route $route
     * @param array $annotations
     *
     * @return RestDoc
     */
    public function getRouteDoc(\ReflectionMethod $method, Route $route, array $annotations)
    {
        $container = new RestDoc();

        $event = new ExtractorEvent($container, $method, $route, $annotations);

        $this->dispatcher->dispatch(ExtractorEvents::HANDLE, $event);
        $this->dispatcher->dispatch(ExtractorEvents::POST_HANDLE, $event);

        return $event->getContainer();
    }

    /**
     * Returns the ReflectionMethod for the given controller string
     *
     * @param string $controller
     *
     * @return null|\ReflectionMethod
     */
    public function getReflectionMethod($controller)
    {
        if (preg_match('#(.+)::([\w]+)#', $controller, $matches)) {
            $class  = $matches[1];
            $method = $matches[2];
        } elseif (preg_match('#(.+):([\w]+)#', $controller, $matches)) {
            $controller = $matches[1];
            $method     = $matches[2];
            if ($this->container->has($controller)) {
                $this->container->enterScope('request');
                $this->container->set('request', new Request(), 'request');
                $class = ClassUtils::getRealClass(get_class($this->container->get($controller)));
                $this->container->leaveScope('request');
            }
        }

        if (isset($class) && isset($method)) {
            try {
                return new \ReflectionMethod($class, $method);
            } catch (\ReflectionException $e) {
            }
        }

        return null;
    }
} 