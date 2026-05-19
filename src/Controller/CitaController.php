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
    public function reservarPaso1(Servicio $servicio, EntityManagerInterface $em): Response
    {
        // Paso 1: Mostrar los locales activos
        $locales = $em->getRepository(Local::class)->findBy(['activo' => true]);

        return $this->render('cita/reservar_paso1.html.twig', [
            'servicio' => $servicio,
            'locales'  => $locales,
        ]);
    }

    #[Route('/reservar/{servicioId}/{localId}', name: 'app_reservar_paso2')]
    public function reservarPaso2(
        int $servicioId,
        int $localId,
        Request $request,
        EntityManagerInterface $em,
        CitaRepository $citaRepository,
        DiaBloqueadoRepository $diasBloqueadosRepo,
        ReglaHorarioRepository $reglasRepo,
        HorarioRepository $horarioRepo
    ): Response {
        $servicio = $em->getRepository(Servicio::class)->find($servicioId);
        $local = $em->getRepository(Local::class)->find($localId);

        if (!$servicio || !$local) {
            throw $this->createNotFoundException('Servicio o Local no encontrado.');
        }

        $cita = new Cita();
        $cita->setEstado('Pendiente');
        $cita->setLocal($local);

        // Pasamos el local como opción al formulario para filtrar los empleados
        $form = $this->createForm(CitaType::class, $cita, [
            'local' => $local,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Validacion server-side de solapamiento
            $fechaInicio = $cita->getFechaInicio();
            $fechaFin = clone $fechaInicio;
            $fechaFin->modify('+' . $servicio->getDuration() . ' minutes');
            $cita->setFechaFin($fechaFin);

            $solapadas = $citaRepository->findSolapadas($fechaInicio, $fechaFin);
            if (count($solapadas) > 0) {
                $this->addFlash(
                    'error',
                    'Lo sentimos, esa hora ya no está disponible. Por favor elige otra.'
                );
                return $this->redirectToRoute('app_reservar_paso2', ['servicioId' => $servicio->getId(), 'localId' => $local->getId()]);
            }
            // Fin validación server-side

            $cita->setUsuario($this->getUser());
            $cita->addServicio($servicio);

            $em->persist($cita);
            $em->flush();

            return $this->redirectToRoute('app_cliente_perfil', ['cita_confirmada' => 'true']);
        }

        // --- Horas ocupadas ---
        $citasFuturas = $citaRepository->createQueryBuilder('c')
            ->where('c.fechaInicio >= :hoy')
            ->andWhere('c.estado != :estado')
            ->setParameter('hoy', new \DateTime('today'))
            ->setParameter('estado', 'Cancelada')
            ->getQuery()
            ->getResult();

        // Generar slots dinámicamente desde los horarios del local
        $horarios = $horarioRepo->findBy(['local' => $local]);

        $slotsTotales = [];
        foreach ($horarios as $horario) {
            $cursor   = clone $horario->getHoraApertura();
            $cierre   = $horario->getHoraCierre();
            $intervalo = $horario->getIntervaloMinutos();

            while ($cursor < $cierre) {
                $slotsTotales[] = $cursor->format('H:i');
                $cursor->modify("+{$intervalo} minutes");
            }
        }

        $slotsTotales = array_unique($slotsTotales);
        sort($slotsTotales);

        $horasOcupadas = [];
        foreach ($citasFuturas as $c) {
            $dia   = $c->getFechaInicio()->format('Y-m-d');
            $inicio = $c->getFechaInicio()->format('H:i');
            $fin    = $c->getFechaFin()?->format('H:i') ?? $inicio;

            foreach ($slotsTotales as $slot) {
                if ($slot >= $inicio && $slot < $fin) {
                    $horasOcupadas[$dia][] = $slot;
                }
            }
        }

        $diasBloqueados = $diasBloqueadosRepo->findFechasBloqueadasProximos(14, $local->getId());

        $reglas = array_map(function ($r) {
            return [
                'dia'   => $r->getDiaSemana(),
                'desde' => $r->getHoraDesde()?->format('H:i'),
                'hasta' => $r->getHoraHasta()?->format('H:i'),
            ];
        }, $reglasRepo->findBy(['local' => $local]));

        return $this->render('cita/reservar.html.twig', [
            'servicio'      => $servicio,
            'local'         => $local,
            'form'          => $form->createView(),
            'horasOcupadas' => json_encode($horasOcupadas),
            'diasBloqueados' => json_encode($diasBloqueados),
            'reglasHorario' => json_encode($reglas),
            'slotsTotales'  => json_encode(array_values($slotsTotales)),
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

    #[Route('/cita/valorar/{id}', name: 'app_cita_valorar', methods: ['GET', 'POST'])]
    public function valorar(Cita $cita, Request $request, EntityManagerInterface $em): Response
    {
        if ($cita->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes valorar una cita que no es tuya.');
        }

        if ($cita->getValoracion()) {
            $this->addFlash('error_perfil', 'Esta cita ya ha sido valorada.');
            return $this->redirectToRoute('app_cliente_perfil', [], 301, '#historial');
        }

        if ($request->isMethod('POST')) {
            $estrellas = (int) $request->request->get('estrellas');
            $comentario = $request->request->get('comentario');

            if ($estrellas >= 1 && $estrellas <= 5) {
                $cita->setValoracion($estrellas);
                $cita->setComentarioValoracion($comentario);
                
                $em->flush();

                $this->addFlash('success_perfil', '¡Gracias por tu valoración!');
                return $this->redirectToRoute('app_cliente_perfil', [], 301, '#historial');
            }
        }

        return $this->render('cita/valorar.html.twig', [
            'cita' => $cita
        ]);
    }
}