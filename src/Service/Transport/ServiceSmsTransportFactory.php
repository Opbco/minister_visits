<?php

namespace App\Service\Transport;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;

final class ServiceSmsTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): ServiceSmsTransport
    {
        $user = $this->getUser($dsn);
        $password = $this->getPassword($dsn);
        $from = $dsn->getRequiredOption('from');
        $senderName = $dsn->getOption('sender_name');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new ServiceSmsTransport($user, $password, $from, $senderName, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['service-sms'];
    }
}