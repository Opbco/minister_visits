<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

class Redirect404ToHomepageListener
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @var ExceptionEvent $event
     * @return null
     */
    public function onKernelException(ExceptionEvent $event)
    {
        // If not a HttpNotFoundException ignore
        if (!($e = $event->getThrowable()) instanceof NotFoundHttpException  or $event->getRequest()->isXmlHttpRequest()) {
            return;
        }
        // Create redirect response with url for the home page
        $response = new RedirectResponse($this->router->generate('app_home', ["page" => $event->getRequest()->getRequestUri()]));

        $event->setResponse($response);
    }
}
