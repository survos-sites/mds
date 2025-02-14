<?php

namespace App\Controller;

use App\Repository\RecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class RowController extends AbstractController
{

    public function __construct(
        private RecordRepository $recordRepository,
    )
    {
    }

    #[Route('/row/{id}', name: 'row_show')]
    public function show(string $id): Response
    {
        $uuId = new Uuid($id);
        $row = $this->recordRepository->find($uuId);
        return $this->render('row/show.html.twig', [
            'row' => $row,
        ]);
    }
}
