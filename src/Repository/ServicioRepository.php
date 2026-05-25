<?php

namespace App\Repository;

use App\Entity\Servicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Servicio>
 */
class ServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Servicio::class);
    }

    /**
     * @return Servicio[]
     */
    public function findActivosPorLocal(\App\Entity\Local $local): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.activo = true')
            ->andWhere('s.local = :local OR s.local IS NULL')
            ->setParameter('local', $local)
            ->getQuery()
            ->getResult();
    }
}
