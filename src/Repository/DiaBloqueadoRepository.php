<?php

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
     * Devuelve array de strings 'YYYY-MM-DD' para los próximos $dias días.
     * Expande automáticamente los rangos (fechaFin) en días individuales.
     */
    public function findFechasBloqueadasProximos(int $dias = 14, ?int $localId = null): array
    {
        $hoy = new \DateTime('today', new \DateTimeZone('Europe/Madrid'));
        $fin = (clone $hoy)->modify("+{$dias} days");

        $qb = $this->createQueryBuilder('d')
            ->where('d.fecha <= :fin')
            ->andWhere('(d.fechaFin IS NULL AND d.fecha >= :hoy) OR (d.fechaFin IS NOT NULL AND d.fechaFin >= :hoy)')
            ->setParameter('hoy', $hoy)
            ->setParameter('fin', $fin);

        if ($localId) {
            $qb->andWhere('d.local = :local')->setParameter('local', $localId);
        }

        $bloques = $qb->getQuery()->getResult();

        $fechas = [];
        foreach ($bloques as $bloque) {
            $desde = clone $bloque->getFecha();
            $hasta = $bloque->getFechaFin() ? clone $bloque->getFechaFin() : clone $desde;

            // Clamp al rango consultado
            if ($desde < $hoy) $desde = clone $hoy;
            if ($hasta > $fin) $hasta = clone $fin;

            $cursor = clone $desde;
            while ($cursor <= $hasta) {
                $fechas[] = $cursor->format('Y-m-d');
                $cursor->modify('+1 day');
            }
        }

        return array_values(array_unique($fechas));
    }
}
