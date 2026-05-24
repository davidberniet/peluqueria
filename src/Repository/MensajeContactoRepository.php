<?php

namespace App\Repository;

use App\Entity\MensajeContacto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MensajeContacto>
 */
class MensajeContactoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MensajeContacto::class);
    }

    public function countNoLeidos(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.leido = false')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
