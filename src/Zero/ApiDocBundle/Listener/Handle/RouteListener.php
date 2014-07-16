<?php


namespace Zero\ApiDocBundle\Listener\Handle;

use Symfony\Component\Routing\Route;
use Zero\ApiDocBundle\Event\ExtractorEvent;
use Zero\ApiDocBundle\RestDoc;
use Zero\ApiDocBundle\Util\DocCommentExtractor;

class RouteListener
{

    /**
     * @var DocCommentExtractor
     */
    private $commentExtractor;

    /**
     * @param DocCommentExtractor $commentExtractor
     */
    public function __construct(DocCommentExtractor $commentExtractor)
    {
        $this->commentExtractor = $commentExtractor;
    }

    /**
     * Handle
     *
     * @param ExtractorEvent $event
     */
    public function onExtractorHandle(ExtractorEvent $event)
    {
        $container = $event->getContainer();
        $method    = $event->getMethod();
        $route     = $event->getRoute();

        $container->setRoute($route);

        $requirements = $container->getRequirements();
        foreach ($route->getRequirements() as $name => $value) {
            if (!isset($requirements[$name]) && '_method' !== $name && '_scheme' !== $name) {
                $options = array(
                    'requirement' => $value,
                    'dataType'    => '',
                    'description' => '',
                );
                $container->addRequirement($name, $options);
            }

            if ('_schema' === $name) {
                $https = ('https' == $value);
                $container->setHttps($https);
            }
        }

        if (method_exists($route, 'getSchemes')) {
            $container->setHttps(in_array('https', $route->getSchemes()));
        }
    }
}