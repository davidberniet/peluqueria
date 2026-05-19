<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Local;
use App\Repository\UserRepository;

class ApiController extends AbstractController
{
    #[Route('/api/productos', name: 'api_productos', methods: ['GET'])]
    public function getProductos(): JsonResponse
    {
        $productosFalsos = [
            [
                'id' => 1,
                'nombre' => 'Champú Revitalizante',
                'precio' => 15.99,
                'stock' => 50
            ],
            [
                'id' => 2,
                'nombre' => 'Acondicionador de Argán',
                'precio' => 18.50,
                'stock' => 30
            ]
        ];

        return $this->json([
            'success' => true,
            'message' => '¡Misión cumplida! Productos obtenidos correctamente.',
            'data' => $productosFalsos
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