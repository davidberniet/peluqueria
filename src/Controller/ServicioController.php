<?php

namespace App\Controller;

use App\Repository\ServicioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServicioController extends AbstractController
{
    #[Route('/servicios', name: 'app_servicios')]
    public function index(ServicioRepository $servicioRepository): Response
    {
        // Solo buscamos los servicios que estén marcados como activos
        $servicios = $servicioRepository->findBy(['activo' => true]);

        return $this->render('main/servicios.html.twig', [
            'servicios' => $servicios,
        ]);
    }
}