<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Local;
use App\Repository\ProductoRepository;
use App\Repository\UserRepository;

class ApiController extends AbstractController
{
    #[Route('/api/productos', name: 'api_productos', methods: ['GET'])]
    public function getProductos(ProductoRepository $productoRepository): JsonResponse
    {
        $productos = $productoRepository->findAll();

        $data = array_map(fn($p) => [
            'id'          => $p->getId(),
            'nombre'      => $p->getNombre(),
            'marca'       => $p->getMarca(),
            'descripcion' => $p->getDescripcion(),
            'precio'      => $p->getPrecio(),
            'stock'       => $p->getStock(),
            'imagen'      => $p->getImagen(),
        ], $productos);

        return $this->json([
            'success' => true,
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    #[Route('/api/empleados-por-local/{id}', name: 'api_empleados_local', methods: ['GET'])]
    public function getEmpleadosPorLocal(Local $local, UserRepository $userRepository): JsonResponse
    {
        $empleados = $userRepository->findEmpleadosByLocal($local);
        $data = [];
        foreach ($empleados as $emp) {
            $data[] = [
                'id' => $emp->getId(),
                'nombre' => $emp->getNombre()
            ];
        }

        return $this->json($data);
    }
}