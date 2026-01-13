<?php

namespace App\EventListener;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Notifier\Event\SentMessageEvent;

class SentMessageListener
{

    public function __construct(private EntityManagerInterface $em)
    {

    }

    public function sentEvent(SentMessageEvent $event)
    {
        // gets the message instance
        $message = $event->getMessage();

    }
}
