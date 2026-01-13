<?php

namespace App\Controller;

use App\Service\Html2PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private $pdfService;

    public function __construct(Html2PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        return $this->render(
            'home/index.html.twig',
            ['page' => $request->query->all()]
        );
    }
}
