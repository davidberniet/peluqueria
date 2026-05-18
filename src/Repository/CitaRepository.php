<?php

namespace App\Repository;

use App\Entity\Cita;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cita>
 */
class CitaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cita::class);
    }

    /**
     * Devuelve las citas activas que se solapan con el rango [inicio, fin).
     * Se usa para la validación server-side antes de persistir una nueva cita.
     *
     * Una cita C solapa con [inicio, fin) si:
     *   C.fechaInicio < fin  AND  C.fechaFin > inicio
     *
     * @param int|null $excluirId ID de cita a excluir (útil para edición futura)
     * @return Cita[]
     */
    public function findSolapadas(\DateTime $inicio, \DateTime $fin, ?int $excluirId = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.estado != :cancelada')
            ->andWhere('c.fechaInicio < :fin')
            ->andWhere('c.fechaFin > :inicio')
            ->setParameter('cancelada', 'Cancelada')
            ->setParameter('inicio', $inicio)
            ->setParameter('fin', $fin);

        if ($excluirId !== null) {
            $qb->andWhere('c.id != :excluirId')
               ->setParameter('excluirId', $excluirId);
        }

        return $qb->getQuery()->getResult();
    }
}
