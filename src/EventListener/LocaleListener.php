<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // If the request has no session, it's stateless, so we return immediately.
        if (!$request->hasSession()) {
            return;
        }

        // Return if the request pathinfo doesn't contain 'admin'
        if (strpos($request->getPathInfo(), 'admin') === false) {
            return;
        }

        // The original checks, which will only run if a session exists.
        if (!$request->hasPreviousSession() || $request->isXmlHttpRequest()) {
            return;
        }

        $locale = $request->getSession()->get('_locale');

        if ($locale) {
            $request->setLocale($locale);
        }
    }
}
