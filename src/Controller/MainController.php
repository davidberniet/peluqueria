<?php

namespace App\Controller;

use App\Entity\Local;
use App\Repository\ServicioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'Peluquería Venus',
        ]);
    }

    #[Route('/servicios', name: 'app_servicios')]
    public function servicios(ServicioRepository $repo): Response
    {
        // Buscamos solo los servicios que estén activos
        $misServicios = $repo->findBy(['activo' => true]);

        // Se los pasamos a una plantilla nueva
        return $this->render('main/servicios.html.twig', [
            'servicios' => $misServicios,
        ]);
    }

    #[Route('/contacto', name: 'app_contacto')]
    public function contacto(EntityManagerInterface $em): Response
    {
        $local = $em->getRepository(Local::class)->findOneBy([]);

        return $this->render('main/contacto.html.twig', [
            'local' => $local,
        ]);
    }
}