<?php

namespace App\Controller;


use App\Entity\Producto;
use App\Form\ProductoType;
use App\Repository\ProductoRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\Cita;
use App\Repository\CitaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Servicio;
use App\Form\ServicioType;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(CitaRepository $citaRepository): Response
    {
        $citas = $citaRepository->findBy([], ['fechaInicio' => 'ASC']);

        return $this->render('admin/index.html.twig', [
            'citas' => $citas,
        ]);
    }

    // --- MAGIA NUEVA: CONFIRMAR CITA ---
    #[Route('/cita/{id}/confirmar', name: 'app_admin_cita_confirmar')]
    public function confirmarCita(Cita $cita, EntityManagerInterface $em): Response
    {
        // Cambiamos el estado
        $cita->setEstado('Confirmada');
        $em->flush(); // Guardamos en la base de datos

        return $this->redirectToRoute('app_admin_dashboard');
    }

    // --- MAGIA NUEVA: CANCELAR CITA ---
    #[Route('/cita/{id}/cancelar', name: 'app_admin_cita_cancelar')]
    public function cancelarCita(Cita $cita, EntityManagerInterface $em): Response
    {
        // Puedes borrarla del todo con $em->remove($cita), pero es mejor 
        // cambiar el estado a 'Cancelada' para tener un historial.
        $cita->setEstado('Cancelada');
        $em->flush();

        return $this->redirectToRoute('app_admin_dashboard');
    }

    // --- LISTA DE SERVICIOS EN EL PANEL ---
    #[Route('/servicios', name: 'app_admin_servicios')]
    public function gestionServicios(\App\Repository\ServicioRepository $servicioRepository): Response
    {
        return $this->render('admin/servicios.html.twig', [
            // Aquí traemos TODOS los servicios, incluso los inactivos, para que el Jefe los vea
            'servicios' => $servicioRepository->findAll(),
        ]);
    }

    // --- CREAR O EDITAR UN SERVICIO ---
    #[Route('/servicio/nuevo', name: 'app_admin_servicio_nuevo')]
    #[Route('/servicio/{id}/editar', name: 'app_admin_servicio_editar')]
    public function formServicio(Request $request, EntityManagerInterface $em, Servicio $servicio = null): Response
    {
        // Si no le pasamos un servicio por la URL, significa que estamos creando uno NUEVO
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

    // --- "BORRAR" (DESACTIVAR) UN SERVICIO ---
    #[Route('/servicio/{id}/eliminar', name: 'app_admin_servicio_eliminar')]
    public function eliminarServicio(Servicio $servicio, EntityManagerInterface $em): Response
    {
        // En lugar de borrarlo y romper citas antiguas, lo ocultamos del catálogo
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
    public function formProducto(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, Producto $producto = null): Response
    {
        $editando = true;
        if (!$producto) {
            $producto = new Producto();
            $editando = false;
        }

        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // LÓGICA DE LA IMAGEN
            $imagenFile = $form->get('imagen')->getData();
            if ($imagenFile) {
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imagenFile->guessExtension();

                try {
                    // Movemos el archivo a la carpeta public/uploads/productos
                    $imagenFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/productos',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Si falla la subida
                }

                // Guardamos solo el nombre en la base de datos
                $producto->setImagen($newFilename);
            }

            $em->persist($producto);
            $em->flush();

            return $this->redirectToRoute('app_admin_productos');
        }

        return $this->render('admin/producto_form.html.twig', [
            'form' => $form->createView(),
            'editando' => $editando,
            'producto' => $producto // Lo pasamos por si queremos mostrar la foto actual al editar
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
}


