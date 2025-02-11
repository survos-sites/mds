<?php

namespace App\Entity;

use App\Repository\RecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecordRepository::class)]
class Record
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private ?int  $id = null,

        #[ORM\Column(options: ['jsonb' => true])]
        private array $data = [],
    ) {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

}
