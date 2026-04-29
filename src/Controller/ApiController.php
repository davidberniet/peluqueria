<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

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
}