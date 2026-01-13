<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthenticationSuccessListener
{
    private bool $isSecureEnv;

    public function __construct(private JWTTokenManagerInterface $jwtManager, RequestStack $requestStack)
    {
        $this->isSecureEnv = $requestStack->getCurrentRequest()->isSecure(); // Utilisez cette variable pour déterminer si vous êtes en environnement sécurisé
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        $response = $event->getResponse(); // Récupérez l'objet Response

        if (!$user instanceof UserInterface) {
            return;
        }

        // 1. Générez le JWT
        // Par défaut, le bundle a déjà généré un JWT et l'a mis dans $data['token'].
        // Vous pouvez le réutiliser ou le régénérer si besoin, mais réutiliser celui qui est là est plus simple.
        $jwt = $data['token'] ?? $this->jwtManager->create($user); // Utilisez celui de l'event ou générez-en un

        // 2. Définissez le cookie HttpOnly
        // La durée de vie du cookie doit correspondre à l'expiration de votre JWT (définie dans lexik_jwt_authentication.yaml)
        // Disons que votre JWT expire dans 3600 secondes (1 heure)
        $jwtExpiration = (new \DateTime('+3600 seconds'))->getTimestamp(); // Exemple: 1 heure à partir de maintenant
        // Ou mieux, récupérez la durée de vie du token depuis la configuration du bundle si disponible
        // $jwtExpiration = time() + $this->jwtManager->getTtl(); // Ça ne marche pas directement, besoin d'injecter la config ou de passer le TTL

        // Une approche plus robuste pour l'expiration du cookie :
        // La méthode $this->jwtManager->create() utilise le TTL configuré.
        // On peut soit le récupérer directement via les paramètres Symfony,
        // soit définir une date d'expiration fixe pour le cookie qui correspond au TTL du JWT.
        // Ici, je vais utiliser une date DateTime pour plus de clarté.
        $cookieExpiration = new \DateTime('+1 hour'); // Ajustez ceci pour correspondre à votre 'token_ttl' configuré

        // Determine if the cookie should be secure based on the environment
        // For development (HTTP), this will be false. For production (HTTPS), it will be true.
        $secureCookie = $this->isSecureEnv; // Use the injected parameter

        $response->headers->setCookie(new Cookie(
            'authToken', // Nom du cookie
            $jwt,         // Valeur du JWT
            $cookieExpiration, // Date d'expiration
            '/',          // Chemin
            null,         // Domaine (null pour le domaine de l'application)
            $secureCookie, // Secure (true pour HTTPS)
            true,         // HttpOnly (NON ACCESSIBLE PAR JAVASCRIPT)
            false,        // Raw (false, Symfony gère l'encodage)
            'Lax'         // SameSite (Lax, Strict, None)
        ));

        unset($data['token']); // Supprime le token du corps JSON

        $event->setData($data);
    }
}
