<?php


namespace Zero\ApiBaseBundle\Tests\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zero\ApiBaseBundle\Listener\InvalidFormExceptionListener;

class InvalidFormExceptionListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var InvalidFormExceptionListener|\Mockery\MockInterface
     */
    public $listener;

    /**
     * @var \Symfony\Component\DependencyInjection\Container|\Mockery\MockInterface
     */
    public $container;

    /**
     * @var \FOS\RestBundle\View\ViewHandlerInterface|\Mockery\MockInterface
     */
    public $viewHandler;

    public function setUp()
    {
        $this->viewHandler = \Mockery::mock('FOS\RestBundle\View\ViewHandlerInterface');
        $this->viewHandler->shouldReceive('isFormatTemplating')->with('html')->andReturn(false);

        $this->container = \Mockery::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->shouldReceive('get')->with('fos_rest.view_handler')->andReturn($this->viewHandler);
        $this->listener  = new InvalidFormExceptionListener($this->container);
    }

    public function getResponseEvent(Request $request)
    {
        $event = \Mockery::mock('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent');
        $event->shouldReceive('getKernel')->andReturn(\Mockery::mock('Symfony\Component\HttpKernel\HttpKernelInterface'));
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->shouldReceive('getRequest')->andReturn($request);

        return $event;
    }

    public function testOnKernelException()
    {
        $request = new Request();
        $response = new Response();

        $event = $this->getResponseEvent($request);

        $exception = \Mockery::mock('\Zero\ApiBaseBundle\Exception\InvalidFormException');
        $exception->shouldReceive('getForm')->andReturn('FormInterface');
        $event->shouldReceive('getException')->andReturn($exception);
        $event->shouldReceive('setResponse')->with($response);
        $event->shouldReceive('stopPropagation');

        $this->container->shouldReceive('getParameter')->with('fos_rest.view_response_listener.force_view')->andReturn(true);
        $this->viewHandler->shouldReceive('handle')->andReturn($response);
        $this->listener->onKernelException($event);
    }
}