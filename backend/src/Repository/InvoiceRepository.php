<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findByNumber(string $number): ?Invoice
    {
        return $this->findOneBy(['number' => $number]);
    }

    public function paginate(int $page, int $perPage, ?InvoiceStatus $status): Paginator
    {
        $qb = $this->createQueryBuilder('i')
            ->orderBy('i.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        if (null !== $status) {
            $qb->andWhere('i.status = :status')
                ->setParameter('status', $status);
        }

        return new Paginator($qb);
    }
}
