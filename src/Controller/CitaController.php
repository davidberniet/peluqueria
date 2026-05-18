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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment as TwigEnvironment;

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
        HorarioRepository $horarioRepo,
        MailerInterface $mailer,
        TwigEnvironment $twig
    ): Response {
        $cita = new Cita();
        $cita->setEstado('Pendiente');

        $local = $em->getRepository(Local::class)->findOneBy([]);
        if ($local) {
            $cita->setLocal($local);
        }

        // Punto 5: ya NO asignamos el empleado hardcodeado aquí.
        // El formulario CitaType gestiona la selección (campo 'empleado' filtrado por rol).

        $form = $this->createForm(CitaType::class, $cita);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // --- Punto 4: Validación server-side de solapamiento ---
            $fechaInicio = $cita->getFechaInicio();
            $fechaFin = clone $fechaInicio;
            $fechaFin->modify('+' . $servicio->getDuration() . ' minutes');
            $cita->setFechaFin($fechaFin);

            $solapadas = $citaRepository->findSolapadas($fechaInicio, $fechaFin);
            if (count($solapadas) > 0) {
                $this->addFlash(
                    'error',
                    '⚠️ Lo sentimos, esa hora ya no está disponible. Por favor elige otra.'
                );
                return $this->redirectToRoute('app_reservar', ['id' => $servicio->getId()]);
            }
            // --- Fin validación server-side ---

            $cita->setUsuario($this->getUser());
            $cita->addServicio($servicio);

            $em->persist($cita);
            $em->flush();

            // --- Punto 2: Envío de email de confirmación ---
            try {
                $htmlContent = $twig->render('emails/confirmacion_cita.html.twig', [
                    'cita'    => $cita,
                    'app_url' => $request->getSchemeAndHttpHost(),
                ]);

                $email = (new Email())
                    ->from('noreply@venus-peluqueria.com')
                    ->to($cita->getUsuario()->getEmail())
                    ->subject('✂️ ¡Tu cita en Venus está confirmada!')
                    ->html($htmlContent);

                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                // El email falla silenciosamente: la reserva ya está guardada
                // En producción se podría loguear: $this->logger->error(...)
            }
            // --- Fin envío email ---

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
        $horarios = $local ? $horarioRepo->findBy(['local' => $local]) : [];

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

        $localId = $local?->getId();
        $diasBloqueados = $diasBloqueadosRepo->findFechasBloqueadasProximos(14, $localId);

        $reglas = $local ? array_map(function ($r) {
            return [
                'dia'   => $r->getDiaSemana(),
                'desde' => $r->getHoraDesde()?->format('H:i'),
                'hasta' => $r->getHoraHasta()?->format('H:i'),
            ];
        }, $reglasRepo->findBy(['local' => $local])) : [];

        return $this->render('cita/reservar.html.twig', [
            'servicio'      => $servicio,
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
}