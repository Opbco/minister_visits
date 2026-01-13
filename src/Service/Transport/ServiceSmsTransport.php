<?php

/*
 * This file is part of the Symfony package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Service\Transport;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ServiceSmsTransport extends AbstractTransport
{
    protected const HOST = 'skysms.cm/api/public/sendsms/v1/output=json';

    private string $user;
    private string $password;
    private string $from;
    private ?string $senderName;

    public function __construct(string $user, #[\SensitiveParameter] string $password, string $from, ?string $senderName = null, ?HttpClientInterface $client = null, ?EventDispatcherInterface $dispatcher = null)
    {
        $this->user = $user;
        $this->password = $password;
        $this->from = $from;
        $this->senderName = $senderName;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        if (null !== $this->senderName) {
            return sprintf('service-sms://%s?from=%s&sender_name=%s', $this->getEndpoint(), $this->from, $this->senderName);
        }

        return sprintf('service-sms://%s?from=%s', $this->getEndpoint(), $this->from);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    public function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }
        
        $url = sprintf('https://%s?user=%s&password=%s&sender=%s', $this->getEndpoint(), $this->user, $this->password, $this->senderName);

        $url .= '&phone='.$message->getPhone().'&message='.urlencode($message->getSubject());
        
        $response = $this->client->request('GET', $url);

        $content = $response->toArray();

        if($content['statut'] != "1") {
            $errorMessage = $content['description'] ?? '';

            throw new TransportException(sprintf('Unable to send the SMS: "%s".', $errorMessage), $response);
        }

        return new SentMessage($message, (string) $this);
    }

}