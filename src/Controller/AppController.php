<?php

namespace App\Controller;

use App\Repository\RecordRepository;
use App\Repository\SourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(
        RecordRepository $recordRepository,
        SourceRepository $sourceRepository,
    ): Response
    {
        return $this->render('app/index.html.twig', [
            'count' => $recordRepository->count(),
            'sourceCount' => $sourceRepository->count(),
        ]);
    }
}
