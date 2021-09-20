<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Service\GatewayService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['request'],
        ];
    }

    /**
     * @param ResponseEvent $event
     */
    public function request(ResponseEvent $event)
    {

        $response = $event->getResponse();

        // Set multiple headers simultaneously
        $response->headers->add([
            'Access-Control-Allow-Credentials' => 'true'
        ]);

    }

}