<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    #[Route('/switch-locale/{locale}', name: 'switch_locale')]
    public function switchLocale(Request $request, string $locale): Response
    {
        // Validate locale
        $availableLocales = ['en', 'fr'];
        if (!in_array($locale, $availableLocales)) {
            throw $this->createNotFoundException('Locale not supported');
        }

        // Set locale in session
        $request->getSession()->set('_locale', $locale);

        // Redirect back to the referer or home
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('sonata_admin_dashboard'));
    }
}
