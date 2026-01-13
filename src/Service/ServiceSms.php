<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ServiceSms
{

    private string $email;
    private string $password;
    private string $from;
    private ?string $senderName;
    private $client;

    public function __construct(string $email, #[\SensitiveParameter] string $password, string $from, ?string $senderName = null, ?HttpClientInterface $client = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->from = $from;
        $this->senderName = $senderName;
        $this->client = $client;
    }

    public function doSend(string $phone, string $message)
    {

        $url = 'https://websms.digicomsgroup.com/api/send-sms';

        $args = [
                'email' => $this->email,
                'password' => $this->password,
                'phone_numbers' => $phone,
                'contenu' =>  $message,
                'expediteur' => $this->senderName
            ];

        $response = $this->client->request('POST', $url, [
            'json' => $args,
        ]);

        if (200 !== $response->getStatusCode()) {
            $content = $response->toArray();
            $errorMessage = $content['message'] ?? '';

            throw new TransportException(sprintf('Unable to send the SMS: "%s".', $errorMessage), $response);
        }

        return $message;
    }

}