<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cliente')]
class ClienteController extends AbstractController
{
    #[Route('/perfil', name: 'app_cliente_perfil')]
    public function perfil(): Response
    {
        return $this->render('cliente/index.html.twig', [
            'controller_name' => 'Mi Perfil de Cliente',
        ]);
    }

    #[Route('/reservar', name: 'app_cliente_reservar')]
    public function reservar(): Response
    {
        return $this->render('cliente/index.html.twig', [
            'controller_name' => 'Reservar Cita',
        ]);
    }
}