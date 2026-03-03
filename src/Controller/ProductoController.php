<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Repository\CitaRepository;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductoController extends AbstractController
{
    #[Route('/productos', name: 'app_productos')]
    public function index(ProductoRepository $productoRepository): Response
    {
        return $this->render('producto/index.html.twig', [
            'productos' => $productoRepository->findAll(),
        ]);
    }

    // --- AÑADIR A LA PRÓXIMA CITA ---
    #[Route('/producto/{id}/añadir-cita', name: 'app_producto_add_cita')]
    public function añadirACita(Producto $producto, CitaRepository $citaRepository, EntityManagerInterface $em): Response
    {
        // 1. Comprobamos que el usuario está logueado
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 2. Buscamos la próxima cita futura de este usuario (que no esté cancelada)
        $citasFuturas = $citaRepository->createQueryBuilder('c')
            ->where('c.usuario = :user')
            ->andWhere('c.fechaInicio >= :hoy')
            ->andWhere('c.estado IN (:estados)')
            ->setParameter('user', $user)
            ->setParameter('hoy', new \DateTime('today'))
            ->setParameter('estados', ['Pendiente', 'Confirmada'])
            ->orderBy('c.fechaInicio', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        // 3. Si no tiene citas futuras, no puede reservar el producto
        if (empty($citasFuturas)) {
            $this->addFlash('error', 'No tienes ninguna cita próxima. ¡Reserva una primero!');
            return $this->redirectToRoute('app_servicios');
        }

        // 4. Si tiene cita, le metemos el producto dentro
        $cita = $citasFuturas[0];
        $cita->addProducto($producto);
        $em->flush(); // Guardamos en la base de datos

        $this->addFlash('success', '¡' . $producto->getNombre() . ' añadido a tu cita del ' . $cita->getFechaInicio()->format('d/m/Y') . '!');
        return $this->redirectToRoute('app_cliente_perfil');
    }
}