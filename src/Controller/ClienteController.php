<?php

namespace App\Controller;

use App\Repository\CitaRepository; // <-- ¡Añadido para poder buscar citas!
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
            'controller_name' => 'Mi Perfil',
        ]);
    }

    // --- ¡AQUÍ ESTÁ LA RUTA QUE TE FALTABA! ---
    #[Route('/mis-citas', name: 'app_cliente_citas')]
    public function misCitas(CitaRepository $citaRepository): Response
    {
        // Buscamos las citas del usuario que está conectado
        $citas = $citaRepository->findBy(
            ['usuario' => $this->getUser()],
            ['fechaInicio' => 'DESC']
        );

        return $this->render('cliente/citas.html.twig', [
            'citas' => $citas,
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