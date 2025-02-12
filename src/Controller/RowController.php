<?php

namespace App\Controller;

use App\Repository\RecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RowController extends AbstractController
{

    public function __construct(
        private RecordRepository $recordRepository,
    )
    {
    }

    #[Route('/row/{id}', name: 'row_show')]
    public function show(int $id): Response
    {
        $row = $this->recordRepository->find($id);
        return $this->render('row/show.html.twig', [
            'row' => $row,
        ]);
    }
}
