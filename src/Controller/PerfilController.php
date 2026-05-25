<?php

namespace App\Controller;

use App\Form\CambiarPasswordType;
use App\Form\PerfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PerfilController extends AbstractController
{
    #[Route('/perfil', name: 'app_cliente_perfil')]
    public function perfil(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Formulario de datos personales
        $formPerfil = $this->createForm(PerfilType::class, $user);
        $formPerfil->handleRequest($request);

        if ($formPerfil->isSubmitted() && $formPerfil->isValid()) {
            $em->flush();
            $this->addFlash('success_perfil', 'Datos actualizados correctamente.');
            return $this->redirectToRoute('app_cliente_perfil', [], 302, '#perfil');
        }

        // --- Formulario de cambio de contraseña ---
        $formPassword = $this->createForm(CambiarPasswordType::class);
        $formPassword->handleRequest($request);

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $data = $formPassword->getData();

            // Verificar que la contraseña actual es correcta
            if (!$hasher->isPasswordValid($user, $data['passwordActual'])) {
                $this->addFlash('error_password', 'La contraseña actual no es correcta.');
                return $this->redirectToRoute('app_cliente_perfil', [], 302, '#password');
            }

            $user->setPassword($hasher->hashPassword($user, $data['nuevaPassword']));
            $em->flush();

            $this->addFlash('success_password', 'Contraseña cambiada correctamente.');
            return $this->redirectToRoute('app_cliente_perfil', [], 302, '#password');
        }

        // --- Historial de citas ---
        $citas = $user->getCitas()->toArray();
        usort($citas, fn($a, $b) => $b->getFechaInicio() <=> $a->getFechaInicio());

        // --- Estadísticas ---
        $totalGastado = 0;
        $totalCitas = 0;
        foreach ($citas as $cita) {
            if ($cita->getEstado() !== 'Cancelada') {
                $totalCitas++;
                foreach ($cita->getServicios() as $servicio) {
                    $totalGastado += $servicio->getPrecio();
                }
                foreach ($cita->getProductos() as $producto) {
                    $totalGastado += $producto->getPrecio();
                }
            }
        }

        return $this->render('perfil/index.html.twig', [
            'formPerfil' => $formPerfil->createView(),
            'formPassword' => $formPassword->createView(),
            'citas' => $citas,
            'totalGastado' => $totalGastado,
            'totalCitas' => $totalCitas,
        ]);
    }
}