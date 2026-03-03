<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Cita;
use App\Entity\Local;
use App\Entity\Servicio;
use App\Form\CitaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CitaController extends AbstractController
{
    #[Route('/reservar/{id}', name: 'app_reservar')]
    public function reservar(Servicio $servicio, Request $request, EntityManagerInterface $em, \App\Repository\CitaRepository $citaRepository): Response
    {
        $cita = new Cita();
        $cita->setEstado('Pendiente');
        
        $local = $em->getRepository(Local::class)->findOneBy([]);
        if ($local) {
            $cita->setLocal($local);
        }

        $empleado = $em->getRepository(User::class)->findOneBy([]);
        if ($empleado) {
            $cita->setEmpleado($empleado);
        }

        $form = $this->createForm(CitaType::class, $cita);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cita->setUsuario($this->getUser());
            $cita->addServicio($servicio);
            
            $fechaFin = clone $cita->getFechaInicio();
            $fechaFin->modify('+' . $servicio->getDuration() . ' minutes');
            $cita->setFechaFin($fechaFin);

            $em->persist($cita);
            $em->flush();

            return $this->redirectToRoute('app_cliente_perfil');
        }

        // --- MAGIA NUEVA: BUSCAR HORAS OCUPADAS ---
        // Buscamos todas las citas desde hoy que NO estén canceladas
        $citasFuturas = $citaRepository->createQueryBuilder('c')
            ->where('c.fechaInicio >= :hoy')
            ->andWhere('c.estado != :estado')
            ->setParameter('hoy', new \DateTime('today'))
            ->setParameter('estado', 'Cancelada')
            ->getQuery()
            ->getResult();

        // Creamos un diccionario con las fechas y sus horas ocupadas
        $horasOcupadas = [];
        foreach ($citasFuturas as $c) {
            $dia = $c->getFechaInicio()->format('Y-m-d');
            $hora = $c->getFechaInicio()->format('H:i');
            
            if (!isset($horasOcupadas[$dia])) {
                $horasOcupadas[$dia] = [];
            }
            $horasOcupadas[$dia][] = $hora;
        }

        return $this->render('cita/reservar.html.twig', [
            'servicio' => $servicio,
            'form' => $form->createView(),
            // Le pasamos las horas ocupadas a Javascript en formato JSON
            'horasOcupadas' => json_encode($horasOcupadas), 
        ]);
    }

    #[Route('/cita/cancelar/{id}', name: 'app_cita_cancelar')]
    public function cancelar(Cita $cita, EntityManagerInterface $em): Response
    {
        // MEDIDA DE SEGURIDAD: Comprobamos que la cita es del usuario que está conectado
        if ($cita->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes cancelar una cita que no es tuya.');
        }

        // Borramos la cita de la base de datos
        $em->remove($cita);
        $em->flush();

        // Redirigimos de vuelta a su perfil
        return $this->redirectToRoute('app_cliente_perfil');
    }
}