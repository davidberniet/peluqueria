<?php

namespace App\Controller;

use App\Entity\Local;
use App\Entity\MensajeContacto;
use App\Repository\ServicioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ServicioRepository $repo): Response
    {
        // Cogemos los 3 primeros servicios activos para mostrarlos en la home
        $serviciosDestacados = $repo->findBy(['activo' => true], ['id' => 'ASC'], 3);

        return $this->render('main/index.html.twig', [
            'controller_name' => 'Peluquería Venus',
            'servicios_destacados' => $serviciosDestacados,
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

    #[Route('/contacto/enviar', name: 'app_contacto_enviar', methods: ['POST'])]
    public function enviarContacto(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $nombre  = trim($request->request->get('nombre', ''));
        $email   = trim($request->request->get('email', ''));
        $asunto  = trim($request->request->get('asunto', ''));
        $mensaje = trim($request->request->get('mensaje', ''));

        if (!$nombre || !$email || !$asunto || !$mensaje || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['ok' => false, 'error' => 'Datos incompletos o incorrectos.'], 400);
        }

        $msg = new MensajeContacto();
        $msg->setNombre(substr($nombre, 0, 150));
        $msg->setEmail(substr($email, 0, 180));
        $msg->setAsunto(substr($asunto, 0, 255));
        $msg->setMensaje($mensaje);

        $em->persist($msg);
        $em->flush();

        return $this->json(['ok' => true]);
    }
}