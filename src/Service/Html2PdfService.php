<?php

namespace App\Service;

use Spipu\Html2Pdf\Html2Pdf;
use Twig\Environment;


class Html2PdfService
{

    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function createPdfFromTemplate(string $templatePath, array $parameters): string
    {
        $html = $this->twig->render($templatePath, $parameters);
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8');
        $html2pdf->writeHTML($html);

        return $html2pdf->output('document.pdf', 'S');
    }
}





?>