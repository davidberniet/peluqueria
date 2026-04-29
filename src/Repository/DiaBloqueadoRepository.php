<?php
// src/Repository/DiaBloqueadoRepository.php

namespace App\Repository;

use App\Entity\DiaBloqueado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DiaBloqueadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiaBloqueado::class);
    }

    /**
     * Devuelve un array de strings 'YYYY-MM-DD' para los próximos N días.
     * Formato listo para json_encode y consumir en JS directamente.
     */
    public function findFechasBloqueadasProximos(int $dias = 14, ?int $localId = null): array
    {
        $hoy = new \DateTime('today', new \DateTimeZone('Europe/Madrid'));
        $fin = (clone $hoy)->modify("+{$dias} days");

        $qb = $this->createQueryBuilder('d')
            ->select('d.fecha')
            ->where('d.fecha >= :hoy')
            ->andWhere('d.fecha <= :fin')
            ->setParameter('hoy', $hoy)
            ->setParameter('fin', $fin);

        if ($localId) {
            $qb->andWhere('d.local = :local')
                ->setParameter('local', $localId);
        }

        $results = $qb->getQuery()->getResult();

        // Devuelve ['2025-08-15', '2025-08-20', ...]
        return array_map(
            fn($row) => $row['fecha']->format('Y-m-d'),
            $results
        );
    }
}