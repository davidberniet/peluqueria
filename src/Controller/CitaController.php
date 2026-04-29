<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Cita;
use App\Entity\Local;
use App\Entity\Servicio;
use App\Form\CitaType;
use App\Repository\CitaRepository;
use App\Repository\DiaBloqueadoRepository;
use App\Repository\ReglaHorarioRepository;
use App\Repository\HorarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CitaController extends AbstractController
{
    #[Route('/reservar/{id}', name: 'app_reservar')]
    public function reservar(
        Servicio $servicio,
        Request $request,
        EntityManagerInterface $em,
        CitaRepository $citaRepository,
        DiaBloqueadoRepository $diasBloqueadosRepo,
        ReglaHorarioRepository $reglasRepo,
        HorarioRepository $horarioRepo
    ): Response {
        $cita = new Cita();
        $cita->setEstado('Pendiente');

        $local = $em->getRepository(Local::class)->findOneBy([]);
        if ($local) {
            $cita->setLocal($local);
        }

        $empleado = $em->getRepository(User::class)->findOneBy([]);
        if ($empleado) {
            $cita->setEmpleado($empleado);
        }

        $form = $this->createForm(CitaType::class, $cita);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cita->setUsuario($this->getUser());
            $cita->addServicio($servicio);

            $fechaFin = clone $cita->getFechaInicio();
            $fechaFin->modify('+' . $servicio->getDuration() . ' minutes');
            $cita->setFechaFin($fechaFin);

            $em->persist($cita);
            $em->flush();

            return $this->redirectToRoute('app_cliente_perfil');
        }

        // --- Horas ocupadas (tu lógica original, sin tocar) ---
        $citasFuturas = $citaRepository->createQueryBuilder('c')
            ->where('c.fechaInicio >= :hoy')
            ->andWhere('c.estado != :estado')
            ->setParameter('hoy', new \DateTime('today'))
            ->setParameter('estado', 'Cancelada')
            ->getQuery()
            ->getResult();

        /// ✅ Generar slots dinámicamente desde los horarios del local
        $horarios = $local ? $horarioRepo->findBy(['local' => $local]) : [];

        $slotsTotales = [];
        foreach ($horarios as $horario) {
            $cursor = clone $horario->getHoraApertura();
            $cierre = $horario->getHoraCierre();
            $intervalo = $horario->getIntervaloMinutos();

            // Avanzamos de slot en slot hasta llegar al cierre
            while ($cursor < $cierre) {
                $slotsTotales[] = $cursor->format('H:i');
                $cursor->modify("+{$intervalo} minutes");
            }
        }

        // Eliminar duplicados por si dos franjas se solapan accidentalmente
        $slotsTotales = array_unique($slotsTotales);
        sort($slotsTotales);
        $horasOcupadas = [];
        foreach ($citasFuturas as $c) {
            $dia = $c->getFechaInicio()->format('Y-m-d');
            $inicio = $c->getFechaInicio()->format('H:i');
            $fin = $c->getFechaFin()?->format('H:i') ?? $inicio;

            foreach ($slotsTotales as $slot) {
                // Bloquea el slot si cae dentro del rango [inicio, fin)
                if ($slot >= $inicio && $slot < $fin) {
                    $horasOcupadas[$dia][] = $slot;
                }
            }
        }

        // ✅ NUEVO: obtener los días bloqueados para los próximos 14 días
        // Devuelve un array simple: ['2025-08-15', '2025-08-20', ...]
        $localId = $local?->getId(); // null-safe por si $local no existe
        $diasBloqueados = $diasBloqueadosRepo->findFechasBloqueadasProximos(14, $localId);

        // Serializar las reglas para JS
        // horaDesde/horaHasta son DateTime de tipo TIME — formateamos a 'H:i' o null
        $reglas = $local ? array_map(function ($r) {
            return [
                'dia' => $r->getDiaSemana(),
                'desde' => $r->getHoraDesde()?->format('H:i'),
                'hasta' => $r->getHoraHasta()?->format('H:i'),
            ];
        }, $reglasRepo->findBy(['local' => $local])) : [];

        return $this->render('cita/reservar.html.twig', [
            'servicio' => $servicio,
            'form' => $form->createView(),
            'horasOcupadas' => json_encode($horasOcupadas),
            'diasBloqueados' => json_encode($diasBloqueados),
            'reglasHorario' => json_encode($reglas),
            'slotsTotales' => json_encode(array_values($slotsTotales)),
        ]);
    }

    #[Route('/cita/cancelar/{id}', name: 'app_cita_cancelar')]
    public function cancelar(Cita $cita, EntityManagerInterface $em): Response
    {
        if ($cita->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes cancelar una cita que no es tuya.');
        }

        $em->remove($cita);
        $em->flush();

        return $this->redirectToRoute('app_cliente_perfil', [], 301, '#historial');
    }
}