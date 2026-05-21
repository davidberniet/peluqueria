<?php

namespace App\Repository;

use App\Entity\Cita;
use App\Entity\Local;
use App\Entity\User;
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
     * Devuelve las citas activas que se solapan con el rango [inicio, fin)
     * para un empleado concreto. Se usa en la validación server-side cuando
     * el cliente elige un empleado específico.
     *
     * @return Cita[]
     */
    public function findSolapadasPorEmpleado(
        \DateTime $inicio,
        \DateTime $fin,
        User $empleado,
        ?int $excluirId = null
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->where('c.estado != :cancelada')
            ->andWhere('c.fechaInicio < :fin')
            ->andWhere('c.fechaFin > :inicio')
            ->andWhere('c.empleado = :empleado')
            ->setParameter('cancelada', 'Cancelada')
            ->setParameter('inicio', $inicio)
            ->setParameter('fin', $fin)
            ->setParameter('empleado', $empleado);

        if ($excluirId !== null) {
            $qb->andWhere('c.id != :excluirId')
               ->setParameter('excluirId', $excluirId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Versión legada: solapamiento global sin filtrar por empleado.
     * Se mantiene por compatibilidad pero se prefiere la lógica por local.
     *
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

    /**
     * Dado un local y un rango de fechas futuras, devuelve un mapa
     * ["Y-m-d" => ["HH:MM", ...]] de slots donde TODOS los empleados
     * del local están ocupados simultáneamente.
     *
     * El JS del calendario usa este mapa para pintar horas bloqueadas.
     *
     * @param User[]  $empleados   Lista de empleados activos del local
     * @param Cita[]  $citasFuturas Citas activas del local en el período
     * @param string[] $slotsTotales Todos los slots horarios posibles ("HH:MM")
     * @return array<string, string[]>   ["2025-06-10" => ["10:00", "11:30"], ...]
     */
    public function calcularHorasOcupadasPorLocal(
        array $empleados,
        array $citasFuturas,
        array $slotsTotales
    ): array {
        $numEmpleados = count($empleados);

        // Si no hay empleados, todos los slots están bloqueados
        if ($numEmpleados === 0) {
            $bloqueados = [];
            foreach ($citasFuturas as $cita) {
                $dia    = $cita->getFechaInicio()->format('Y-m-d');
                $inicio = $cita->getFechaInicio()->format('H:i');
                $fin    = $cita->getFechaFin()?->format('H:i') ?? $inicio;
                foreach ($slotsTotales as $slot) {
                    if ($slot >= $inicio && $slot < $fin) {
                        $bloqueados[$dia][] = $slot;
                    }
                }
            }
            return $bloqueados;
        }

        // Construir mapa: slot => ["Y-m-d|HH:MM" => cantidad de empleados ocupados]
        // Un slot queda bloqueado cuando ocupados[$dia][$slot] === $numEmpleados
        $ocupadosPorSlot = []; // ["Y-m-d" => ["HH:MM" => int]]

        foreach ($citasFuturas as $cita) {
            $dia    = $cita->getFechaInicio()->format('Y-m-d');
            $inicio = $cita->getFechaInicio()->format('H:i');
            $fin    = $cita->getFechaFin()?->format('H:i') ?? $inicio;

            foreach ($slotsTotales as $slot) {
                if ($slot >= $inicio && $slot < $fin) {
                    // Contamos cuántos empleados distintos están ocupados en este slot
                    $empId = $cita->getEmpleado()?->getId() ?? 0;
                    $ocupadosPorSlot[$dia][$slot][$empId] = true;
                }
            }
        }

        // Transformar: solo marcar como bloqueado si TODOS los empleados están ocupados
        $horasOcupadas = [];
        foreach ($ocupadosPorSlot as $dia => $slots) {
            foreach ($slots as $slot => $empIds) {
                if (count($empIds) >= $numEmpleados) {
                    $horasOcupadas[$dia][] = $slot;
                }
            }
        }

        return $horasOcupadas;
    }
}
