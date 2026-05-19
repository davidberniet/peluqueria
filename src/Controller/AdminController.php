<?php

namespace App\Controller;

use App\Entity\DiaBloqueado;
use App\Entity\Local;
use App\Entity\Producto;
use App\Entity\ReglaHorario;
use App\Entity\User;
use App\Entity\Cita;
use App\Entity\Horario;
use App\Entity\Servicio;
use App\Form\EmpleadoType;
use App\Form\LocalType;
use App\Form\ProductoType;
use App\Form\ServicioType;
use App\Repository\CitaRepository;
use App\Repository\DiaBloqueadoRepository;
use App\Repository\HorarioRepository;
use App\Repository\ProductoRepository;
use App\Repository\ReglaHorarioRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        CitaRepository $citaRepository,
        UserRepository $userRepository,
        DiaBloqueadoRepository $diasBloqueadosRepo,
        ReglaHorarioRepository $reglaHorarioRepo,
        HorarioRepository $horarioRepo,
        EntityManagerInterface $em
    ): Response {
        $citasTotal = $citaRepository->findBy([], ['fechaInicio' => 'ASC']);

        $citasHoyCount = 0;
        $ingresosHoy   = 0;
        $hoy           = (new \DateTime())->format('Y-m-d');

        foreach ($citasTotal as $cita) {
            if ($cita->getFechaInicio() && $cita->getFechaInicio()->format('Y-m-d') === $hoy) {
                if ($cita->getEstado() !== 'Cancelada') {
                    $citasHoyCount++;
                    foreach ($cita->getServicios() as $servicio) {
                        $ingresosHoy += $servicio->getPrecio();
                    }
                }
            }
        }

        $usuarios      = $userRepository->findAll();
        $totalClientes = count($usuarios) > 0 ? count($usuarios) - 1 : 0;

        $diasBloqueados = $diasBloqueadosRepo->findBy([], ['fecha' => 'ASC']);

        $locales = $em->getRepository(Local::class)->findAll();

        $empleados = $userRepository->findEmpleados();

        return $this->render('admin/index.html.twig', [
            'citas'          => $citasTotal,
            'citas_hoy'      => $citasHoyCount,
            'ingresos_hoy'   => $ingresosHoy,
            'total_clientes' => $totalClientes,
            'diasBloqueados' => $diasBloqueados,
            'reglasHorario'  => $reglaHorarioRepo->findAll(),
            'horarios'       => $horarioRepo->findAll(),
            'empleados'      => $empleados,
            'locales'        => $locales,
        ]);
    }

    // Añadir un día bloqueado desde el formulario del panel
    #[Route('/dias-bloqueados/nuevo', name: 'admin_dias_bloqueados', methods: ['POST'])]
    public function crearDiaBloqueado(Request $request, EntityManagerInterface $em): Response
    {
        $fechaStr = $request->request->get('fecha');
        $motivo = $request->request->get('motivo');

        if ($fechaStr) {
            $local = $em->getRepository(Local::class)->findOneBy([]);

            $dia = new DiaBloqueado();
            $dia->setFecha(new \DateTime($fechaStr));
            $dia->setMotivo($motivo ?: null);

            if ($local) {
                $dia->setLocal($local);
            }

            $em->persist($dia);
            $em->flush();

            $this->addFlash('success', 'Día bloqueado correctamente.');
        }

        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#bloqueados');
    }

    // Eliminar un día bloqueado
    #[Route('/dias-bloqueados/{id}/eliminar', name: 'admin_dias_bloqueados_eliminar')]
    public function eliminarDiaBloqueado(DiaBloqueado $dia, EntityManagerInterface $em): Response
    {
        $em->remove($dia);
        $em->flush();

        $this->addFlash('success', 'Día desbloqueado correctamente.');

        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#bloqueados');
    }

    #[Route('/reglas-horario/nueva', name: 'admin_regla_horario_nueva', methods: ['POST'])]
    public function crearReglaHorario(Request $request, EntityManagerInterface $em): Response
    {
        $local = $em->getRepository(Local::class)->findOneBy([]);

        $regla = new ReglaHorario();
        $regla->setDiaSemana((int) $request->request->get('dia_semana'));
        $regla->setMotivo($request->request->get('motivo') ?: null);
        $regla->setLocal($local);

        // Si vienen las horas, es una franja. Si no, es día completo.
        $desdeStr = $request->request->get('hora_desde');
        $hastaStr = $request->request->get('hora_hasta');

        if ($desdeStr && $hastaStr) {
            $regla->setHoraDesde(new \DateTime($desdeStr));
            $regla->setHoraHasta(new \DateTime($hastaStr));
        }
        // Si están vacías se quedan null → día completo bloqueado

        $em->persist($regla);
        $em->flush();

        $this->addFlash('success', 'Regla añadida correctamente.');
        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#horario');
    }

    #[Route('/reglas-horario/{id}/eliminar', name: 'admin_regla_horario_eliminar')]
    public function eliminarReglaHorario(ReglaHorario $regla, EntityManagerInterface $em): Response
    {
        $em->remove($regla);
        $em->flush();

        $this->addFlash('success', 'Regla eliminada correctamente.');
        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#horario');
    }

    // --- VER TODAS LAS CITAS (HISTORIAL COMPLETO) ---
    #[Route('/citas', name: 'app_admin_citas')]
    public function listaCitas(\App\Repository\CitaRepository $citaRepository): Response
    {
        $todasLasCitas = $citaRepository->findBy([], ['fechaInicio' => 'DESC']);

        return $this->render('admin/citas.html.twig', [
            'citas' => $todasLasCitas,
        ]);
    }

    // --- CONFIRMAR CITA ---
    #[Route('/cita/{id}/confirmar', name: 'app_admin_cita_confirmar')]
    public function confirmarCita(
        Cita $cita, 
        EntityManagerInterface $em,
        MailerInterface $mailer,
        TwigEnvironment $twig,
        Request $request
    ): Response {
        $cita->setEstado('Confirmada');
        $em->flush();

        // Enviar email de confirmación
        try {
            $htmlContent = $twig->render('emails/confirmacion_cita.html.twig', [
                'cita'    => $cita,
                'app_url' => $request->getSchemeAndHttpHost(),
            ]);

            $email = (new Email())
                ->from('noreply@venus-peluqueria.com')
                ->to($cita->getUsuario()->getEmail())
                ->subject('✂️ ¡Tu cita en Venus ha sido confirmada!')
                ->html($htmlContent);

            $mailer->send($email);
            $this->addFlash('success', 'Cita confirmada y cliente notificado.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Cita confirmada, pero hubo un error enviando el email al cliente.');
        }

        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#citas');
    }

    // --- CANCELAR CITA ---
    #[Route('/cita/{id}/cancelar', name: 'app_admin_cita_cancelar')]
    public function cancelarCita(
        Cita $cita, 
        EntityManagerInterface $em,
        MailerInterface $mailer,
        TwigEnvironment $twig,
        Request $request
    ): Response {
        $cita->setEstado('Cancelada');
        $em->flush();

        // Enviar email de cancelación
        try {
            $htmlContent = $twig->render('emails/cancelacion_cita.html.twig', [
                'cita'    => $cita,
                'app_url' => $request->getSchemeAndHttpHost(),
            ]);

            $email = (new Email())
                ->from('noreply@venus-peluqueria.com')
                ->to($cita->getUsuario()->getEmail())
                ->subject('❌ Actualización sobre tu cita en Venus')
                ->html($htmlContent);

            $mailer->send($email);
            $this->addFlash('success', 'Cita cancelada y cliente notificado.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Cita cancelada, pero hubo un error enviando el email al cliente.');
        }

        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#citas');
    }

    // --- LISTA DE SERVICIOS EN EL PANEL ---
    #[Route('/servicios', name: 'app_admin_servicios')]
    public function gestionServicios(\App\Repository\ServicioRepository $servicioRepository): Response
    {
        return $this->render('admin/servicios.html.twig', [
            'servicios' => $servicioRepository->findAll(),
        ]);
    }

    // --- CREAR O EDITAR UN SERVICIO ---
    #[Route('/servicio/nuevo', name: 'app_admin_servicio_nuevo')]
    #[Route('/servicio/{id}/editar', name: 'app_admin_servicio_editar')]
    public function formServicio(Request $request, EntityManagerInterface $em, Servicio $servicio = null): Response
    {
        $editando = true;
        if (!$servicio) {
            $servicio = new Servicio();
            $editando = false;
        }

        $form = $this->createForm(ServicioType::class, $servicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($servicio);
            $em->flush();

            return $this->redirectToRoute('app_admin_servicios');
        }

        return $this->render('admin/servicio_form.html.twig', [
            'form' => $form->createView(),
            'editando' => $editando,
        ]);
    }

    // --- DESACTIVAR UN SERVICIO ---
    #[Route('/servicio/{id}/eliminar', name: 'app_admin_servicio_eliminar')]
    public function eliminarServicio(Servicio $servicio, EntityManagerInterface $em): Response
    {
        $servicio->setActivo(false);
        $em->flush();

        return $this->redirectToRoute('app_admin_servicios');
    }

    // --- LISTA DE PRODUCTOS EN EL PANEL ---
    #[Route('/productos-admin', name: 'app_admin_productos')]
    public function gestionProductos(ProductoRepository $productoRepository): Response
    {
        return $this->render('admin/productos.html.twig', [
            'productos' => $productoRepository->findAll(),
        ]);
    }

    // --- CREAR O EDITAR UN PRODUCTO (CON SUBIDA DE FOTO) ---
    #[Route('/producto/nuevo', name: 'app_admin_producto_nuevo')]
    #[Route('/producto/{id}/editar', name: 'app_admin_producto_editar')]
    public function formProducto(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        Producto $producto = null
    ): Response {
        $editando = true;
        if (!$producto) {
            $producto = new Producto();
            $editando = false;
        }

        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagenFile = $form->get('imagen')->getData();
            if ($imagenFile) {
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imagenFile->guessExtension();

                try {
                    $imagenFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/productos',
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $producto->setImagen($newFilename);
            }

            $em->persist($producto);
            $em->flush();

            return $this->redirectToRoute('app_admin_productos');
        }

        return $this->render('admin/producto_form.html.twig', [
            'form' => $form->createView(),
            'editando' => $editando,
            'producto' => $producto,
        ]);
    }

    // --- ELIMINAR UN PRODUCTO ---
    #[Route('/producto/{id}/eliminar', name: 'app_admin_producto_eliminar')]
    public function eliminarProducto(Producto $producto, EntityManagerInterface $em): Response
    {
        $em->remove($producto);
        $em->flush();

        return $this->redirectToRoute('app_admin_productos');
    }

    #[Route('/horarios/nuevo', name: 'admin_horario_nuevo', methods: ['POST'])]
    public function crearHorario(Request $request, EntityManagerInterface $em): Response
    {
        $local = $em->getRepository(Local::class)->findOneBy([]);

        $horario = new Horario();
        $horario->setHoraApertura(new \DateTime($request->request->get('hora_apertura')));
        $horario->setHoraCierre(new \DateTime($request->request->get('hora_cierre')));
        $horario->setIntervaloMinutos((int) $request->request->get('intervalo', 30));
        $horario->setLocal($local);

        $em->persist($horario);
        $em->flush();

        $this->addFlash('success', 'Franja horaria añadida.');
        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#horario');
    }

    #[Route('/horarios/{id}/eliminar', name: 'admin_horario_eliminar')]
    public function eliminarHorario(Horario $horario, EntityManagerInterface $em): Response
    {
        $em->remove($horario);
        $em->flush();

        $this->addFlash('success', 'Franja horaria eliminada.');
        return $this->redirectToRoute('app_admin_dashboard', [], 301, '#horario');
    }

    #[Route('/cliente/{id}', name: 'app_admin_cliente_ficha')]
    public function fichaCliente(\App\Entity\User $cliente): Response
    {
        // Historial ordenado de más reciente a más antiguo
        $citas = $cliente->getCitas()->toArray();
        usort($citas, fn($a, $b) => $b->getFechaInicio() <=> $a->getFechaInicio());

        // Estadísticas
        $totalGastado    = 0;
        $totalCitas      = 0;
        $citasPendientes = 0;

        foreach ($citas as $cita) {
            if ($cita->getEstado() !== 'Cancelada') {
                $totalCitas++;
                foreach ($cita->getServicios() as $servicio) {
                    $totalGastado += $servicio->getPrecio();
                }
            }
            if ($cita->getEstado() === 'Pendiente') {
                $citasPendientes++;
            }
        }

        return $this->render('admin/cliente_ficha.html.twig', [
            'cliente'         => $cliente,
            'citas'           => $citas,
            'totalGastado'    => $totalGastado,
            'totalCitas'      => $totalCitas,
            'citasPendientes' => $citasPendientes,
        ]);
    }

    // =====================================================================
    // CRUD EMPLEADOS
    // =====================================================================

    #[Route('/empleados', name: 'app_admin_empleados')]
    public function listaEmpleados(UserRepository $userRepository): Response
    {
        return $this->render('admin/empleados.html.twig', [
            'empleados' => $userRepository->findEmpleados(),
        ]);
    }

    #[Route('/empleado/nuevo', name: 'app_admin_empleado_nuevo')]
    #[Route('/empleado/{id}/editar', name: 'app_admin_empleado_editar')]
    public function formEmpleado(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ?User $empleado = null
    ): Response {
        $editando = $empleado !== null;

        if (!$editando) {
            $empleado = new User();
            // Contraseña temporal — el empleado deberá cambiarla
            $empleado->setPassword($hasher->hashPassword($empleado, 'Venus2024!'));
        }

        $form = $this->createForm(EmpleadoType::class, $empleado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($empleado);
            $em->flush();

            $this->addFlash(
                'success',
                $editando ? 'Empleado actualizado correctamente.' : 'Empleado creado. Contraseña inicial: Venus2024!'
            );

            return $this->redirectToRoute('app_admin_empleados');
        }

        return $this->render('admin/empleado_form.html.twig', [
            'form'     => $form->createView(),
            'editando' => $editando,
            'empleado' => $empleado,
        ]);
    }

    #[Route('/empleado/{id}/eliminar', name: 'app_admin_empleado_eliminar')]
    public function eliminarEmpleado(User $empleado, EntityManagerInterface $em): Response
    {
        // Seguridad: no se puede eliminar al propio admin logueado
        if ($empleado === $this->getUser()) {
            $this->addFlash('error', 'No puedes eliminarte a ti mismo.');
            return $this->redirectToRoute('app_admin_empleados');
        }

        // Quitamos el rol en lugar de borrar el usuario (preserva historial de citas)
        $empleado->setRoles([]);
        $em->flush();

        $this->addFlash('success', 'El empleado ha sido degradado a cliente. Sus citas históricas se conservan.');
        return $this->redirectToRoute('app_admin_empleados');
    }

    // =====================================================================
    // CRUD LOCAL
    // =====================================================================

    #[Route('/locales', name: 'app_admin_locales')]
    public function listaLocales(EntityManagerInterface $em): Response
    {
        $locales = $em->getRepository(Local::class)->findAll();

        return $this->render('admin/locales.html.twig', [
            'locales' => $locales,
        ]);
    }

    #[Route('/local/nuevo', name: 'app_admin_local_nuevo')]
    #[Route('/local/{id}/editar', name: 'app_admin_local_editar')]
    public function formLocal(Request $request, EntityManagerInterface $em, ?Local $local = null): Response
    {
        $editando = true;

        if (!$local) {
            $local = new Local();
            $local->setActivo(true);
            $editando = false;
        }

        $form = $this->createForm(LocalType::class, $local);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($local);
            $em->flush();

            $this->addFlash('success', 'Datos del local actualizados correctamente.');
            return $this->redirectToRoute('app_admin_locales');
        }

        return $this->render('admin/local_form.html.twig', [
            'form'     => $form->createView(),
            'local'    => $local,
            'editando' => $editando,
        ]);
    }

    #[Route('/local/{id}/eliminar', name: 'app_admin_local_eliminar')]
    public function eliminarLocal(Local $local, EntityManagerInterface $em): Response
    {
        $local->setActivo(false);
        $em->flush();

        $this->addFlash('success', 'Local desactivado correctamente.');
        return $this->redirectToRoute('app_admin_locales');
    }
}