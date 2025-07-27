<?php

namespace App\Repository;

use App\Entity\Extract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Survos\CoreBundle\Traits\QueryBuilderHelperInterface;
use Survos\CoreBundle\Traits\QueryBuilderHelperTrait;

/**
 * @extends ServiceEntityRepository<Extract>
 */
class ExtractRepository extends ServiceEntityRepository implements QueryBuilderHelperInterface
{
    use QueryBuilderHelperTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Extract::class);
    }

    public function findByTokenCode(string $value): ?Extract
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.tokenCode = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
