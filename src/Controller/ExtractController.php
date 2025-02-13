<?php

namespace App\Controller;

use App\Repository\ExtractRepository;
use App\Repository\RecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExtractController extends AbstractController
{

    public function __construct(
        private ExtractRepository $extractRepository,
    )
    {
    }

    #[Route('/extract/{tokenCode}', name: 'extract_show')]
    public function show(string $tokenCode): Response
    {
        $extract = $this->extractRepository->findOneBy(['tokenCode' => $tokenCode]);
        return $this->render('extract/show.html.twig', [
            'extract' => $extract,
        ]);
    }
}
