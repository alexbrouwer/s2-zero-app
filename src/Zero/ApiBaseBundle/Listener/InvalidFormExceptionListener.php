<?php


namespace Zero\ApiBaseBundle\Listener;

use FOS\RestBundle\EventListener\ViewResponseListener;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Zero\ApiBaseBundle\Exception\InvalidFormException;

class InvalidFormExceptionListener extends ViewResponseListener
{
    /**
     * Method that gets called if the kernel throws an exception
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof InvalidFormException) {
            $viewEvent = new GetResponseForControllerResultEvent(
                $event->getKernel(),
                $event->getRequest(),
                $event->getRequestType(),
                $exception->getForm()
            );

            parent::onKernelView($viewEvent);

            $event->setResponse($viewEvent->getResponse());
            if ($viewEvent->isPropagationStopped()) {
                $event->stopPropagation();
            }
        }
    }
}