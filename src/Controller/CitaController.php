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
use App\Repository\ServicioRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CitaController extends AbstractController
{
    /**
     * Paso 0 (nuevo): el cliente llega desde la página de servicios y elige el local.
     * Desde aquí ya puede venir con un servicio preseleccionado o sin ninguno.
     * Mantenemos compatibilidad con la ruta antigua {id} para no romper los enlaces existentes.
     */
    #[Route('/reservar/{id}', name: 'app_reservar')]
    public function reservarPaso1(Servicio $servicio, EntityManagerInterface $em): Response
    {
        if ($servicio->getLocal() !== null) {
            $locales = [$servicio->getLocal()];
        } else {
            $locales = $em->getRepository(Local::class)->findBy(['activo' => true]);
        }

        return $this->render('cita/reservar_paso1.html.twig', [
            'servicio' => $servicio,
            'locales'  => $locales,
        ]);
    }

    /**
     * Nuevo paso intermedio: el cliente elige QUÉ servicios quiere (multi-select)
     * dentro del local elegido. Si ya viene con un servicio preseleccionado,
     * aparece marcado por defecto.
     */
    #[Route('/seleccionar-servicios/{localId}', name: 'app_seleccionar_servicios')]
    public function seleccionarServicios(
        int $localId,
        Request $request,
        EntityManagerInterface $em,
        ServicioRepository $servicioRepository
    ): Response {
        $local = $em->getRepository(Local::class)->find($localId);
        if (!$local) {
            throw $this->createNotFoundException('Local no encontrado.');
        }

        // Servicios activos del local (incluyendo globales)
        $servicios = $servicioRepository->findActivosPorLocal($local);

        // Si venimos de la página de servicios con uno preseleccionado
        $preseleccionado = (int) $request->query->get('servicioId', 0);

        return $this->render('cita/seleccionar_servicios.html.twig', [
            'local'          => $local,
            'servicios'      => $servicios,
            'preseleccionado' => $preseleccionado,
        ]);
    }

    /**
     * Paso 2: calendario + hora + empleado.
     * Acepta uno o varios servicioIds separados por coma: ?servicioIds=1,3,5
     * Para mantener compatibilidad con enlaces antiguos también admite servicioId=X.
     */
    #[Route('/reservar/{servicioId}/{localId}', name: 'app_reservar_paso2')]
    public function reservarPaso2(
        int $servicioId,
        int $localId,
        Request $request,
        EntityManagerInterface $em,
        CitaRepository $citaRepository,
        DiaBloqueadoRepository $diasBloqueadosRepo,
        ReglaHorarioRepository $reglasRepo,
        HorarioRepository $horarioRepo,
        UserRepository $userRepository,
        ServicioRepository $servicioRepository
    ): Response {
        $servicio = $em->getRepository(Servicio::class)->find($servicioId);
        $local    = $em->getRepository(Local::class)->find($localId);

        if (!$servicio || !$local) {
            throw $this->createNotFoundException('Servicio o Local no encontrado.');
        }

        // --- Soporte multi-servicios ---
        // Leemos la lista de IDs desde el query param ?servicioIds=1,3,5
        // Si no existe, usamos el servicioId de la ruta (compatibilidad con enlaces viejos)
        $servicioIdsRaw = $request->query->get('servicioIds', (string) $servicioId);
        $servicioIds    = array_filter(
            array_map('intval', explode(',', $servicioIdsRaw)),
            fn($id) => $id > 0
        );

        // Cargamos todos los servicios seleccionados
        $serviciosSeleccionados = $servicioRepository->findBy(['id' => array_values($servicioIds)]);

        // Si no hay ninguno válido, usamos el de la ruta como fallback
        if (empty($serviciosSeleccionados)) {
            $serviciosSeleccionados = [$servicio];
        }

        // Calcular duración y precio totales
        $duracionTotal = 0;
        $precioTotal   = 0.0;
        foreach ($serviciosSeleccionados as $s) {
            $duracionTotal += $s->getDuration();
            $precioTotal   += $s->getPrecio();
        }

        $cita = new Cita();
        $cita->setEstado('Pendiente');
        $cita->setLocal($local);

        // La fecha la pone el calendario JS en un input oculto (cita[fechaInicio]) que NO es
        // un campo del formulario. La asignamos —junto con la fechaFin calculada— ANTES de
        // handleRequest, porque la validación de Symfony se ejecuta durante el submit (evento
        // POST_SUBMIT). Si esperáramos a después de isValid(), seguirían nulas al validar.
        if ($request->isMethod('POST')) {
            $citaData = $request->request->all('cita');
            if (!empty($citaData['fechaInicio'])) {
                try {
                    $inicio = new \DateTime($citaData['fechaInicio']);
                    $cita->setFechaInicio($inicio);
                    $cita->setFechaFin((clone $inicio)->modify('+' . $duracionTotal . ' minutes'));
                } catch (\Exception $e) {
                    // formato inválido → la aserción NotNull se encargará del error
                }
            }
        }

        // Pasamos el local como opción al formulario para filtrar los empleados
        $form = $this->createForm(CitaType::class, $cita, [
            'local' => $local,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cita->setUsuario($this->getUser());

            foreach ($serviciosSeleccionados as $s) {
                $cita->addServicio($s);
            }

            $fechaInicio = $cita->getFechaInicio();
            $fechaFin    = $cita->getFechaFin();

            // --- Validación server-side inteligente por empleado ---
            $empleadoElegido = $cita->getEmpleado();

            if ($empleadoElegido !== null) {
                // El cliente pidió un empleado concreto:
                // solo bloqueamos si ESE empleado ya tiene cita en ese rango
                $solapadas = $citaRepository->findSolapadasPorEmpleado($fechaInicio, $fechaFin, $empleadoElegido);
            } else {
                // Sin preferencia: buscar si hay algún empleado libre
                $empleados = $userRepository->findEmpleadosByLocal($local);
                $empleadoLibre = null;

                foreach ($empleados as $emp) {
                    $ocupado = $citaRepository->findSolapadasPorEmpleado($fechaInicio, $fechaFin, $emp);
                    if (count($ocupado) === 0) {
                        $empleadoLibre = $emp;
                        break;
                    }
                }

                if ($empleadoLibre !== null) {
                    // Asignar automáticamente el primer empleado libre
                    $cita->setEmpleado($empleadoLibre);
                    $solapadas = []; // hay hueco
                } else {
                    $solapadas = [new \stdClass()]; // todos ocupados → bloqueado
                }
            }
            // --- Fin validación ---

            if (count($solapadas) > 0) {
                $this->addFlash(
                    'error',
                    'Lo sentimos, esa hora ya no está disponible. Por favor elige otra.'
                );
                return $this->redirectToRoute('app_reservar_paso2', [
                    'servicioId' => $servicio->getId(),
                    'localId'    => $local->getId(),
                    'servicioIds' => implode(',', array_map(fn($s) => $s->getId(), $serviciosSeleccionados)),
                ]);
            }

            $em->persist($cita);
            $em->flush();

            return $this->redirectToRoute('app_cliente_perfil', ['cita_confirmada' => 'true']);
        }


        // --- Slots horarios del local ---
        $horarios = $horarioRepo->findBy(['local' => $local]);

        $slotsTotales = [];
        foreach ($horarios as $horario) {
            $cursor    = clone $horario->getHoraApertura();
            $cierre    = $horario->getHoraCierre();
            $intervalo = max(1, $horario->getIntervaloMinutos());

            while ($cursor < $cierre) {
                $slotsTotales[] = $cursor->format('H:i');
                $cursor->modify("+{$intervalo} minutes");
            }
        }
        $slotsTotales = array_values(array_unique($slotsTotales));
        sort($slotsTotales);

        // --- Citas futuras del local (no canceladas) ---
        $citasFuturas = $citaRepository->createQueryBuilder('c')
            ->where('c.fechaInicio >= :hoy')
            ->andWhere('c.estado != :estado')
            ->andWhere('c.local = :local')
            ->setParameter('hoy', new \DateTime('today'))
            ->setParameter('estado', 'Cancelada')
            ->setParameter('local', $local)
            ->getQuery()
            ->getResult();

        // --- Empleados activos del local ---
        $empleados = $userRepository->findEmpleadosByLocal($local);

        // --- Horas bloqueadas para el JS (inteligente: solo cuando todos ocupados) ---
        $horasOcupadas = $citaRepository->calcularHorasOcupadasPorLocal(
            $empleados,
            $citasFuturas,
            $slotsTotales
        );

        $diasBloqueados = $diasBloqueadosRepo->findFechasBloqueadasProximos(90, $local->getId());

        $reglas = array_map(function ($r) {
            return [
                'dia'   => $r->getDiaSemana(),
                'desde' => $r->getHoraDesde()?->format('H:i'),
                'hasta' => $r->getHoraHasta()?->format('H:i'),
            ];
        }, $reglasRepo->findBy(['local' => $local]));

        return $this->render('cita/reservar.html.twig', [
            'servicio'           => $servicio,             // primer servicio (compatibilidad)
            'serviciosSeleccionados' => $serviciosSeleccionados,
            'duracionTotal'      => $duracionTotal,
            'precioTotal'        => $precioTotal,
            'servicioIds'        => implode(',', array_map(fn($s) => $s->getId(), $serviciosSeleccionados)),
            'local'              => $local,
            'form'               => $form->createView(),
            'horasOcupadas'      => json_encode($horasOcupadas),
            'diasBloqueados'     => json_encode($diasBloqueados),
            'reglasHorario'      => json_encode($reglas),
            'slotsTotales'       => json_encode(array_values($slotsTotales)),
        ]);
    }

    #[Route('/cita/cancelar/{id}', name: 'app_cita_cancelar')]
    public function cancelar(Cita $cita, EntityManagerInterface $em): Response
    {
        if ($cita->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes cancelar una cita que no es tuya.');
        }

        // Soft delete: marcamos como cancelada en lugar de borrar físicamente.
        // Así se conserva el historial del cliente y las estadísticas del local.
        $cita->setEstado('Cancelada');
        $em->flush();

        $this->addFlash('success', 'Tu cita ha sido cancelada correctamente.');
        return $this->redirectToRoute('app_cliente_perfil', [], 302, '#historial');
    }

    #[Route('/cita/valorar/{id}', name: 'app_cita_valorar', methods: ['GET', 'POST'])]
    public function valorar(Cita $cita, Request $request, EntityManagerInterface $em): Response
    {
        if ($cita->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes valorar una cita que no es tuya.');
        }

        if ($cita->getValoracion()) {
            $this->addFlash('error_perfil', 'Esta cita ya ha sido valorada.');
            return $this->redirectToRoute('app_cliente_perfil', [], 302, '#historial');
        }

        if ($request->isMethod('POST')) {
            $estrellas = (int) $request->request->get('estrellas');
            $comentario = $request->request->get('comentario');

            if ($estrellas >= 1 && $estrellas <= 5) {
                $cita->setValoracion($estrellas);
                $cita->setComentarioValoracion($comentario);
                
                $em->flush();

                $this->addFlash('success_perfil', '¡Gracias por tu valoración!');
                return $this->redirectToRoute('app_cliente_perfil', [], 302, '#historial');
            }
        }

        return $this->render('cita/valorar.html.twig', [
            'cita' => $cita
        ]);
    }
}