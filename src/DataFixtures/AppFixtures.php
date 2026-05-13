<?php

namespace App\DataFixtures;

use App\Entity\Servicio;
use App\Entity\Local;
use App\Entity\Producto;
use App\Entity\Horario;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    // Encriptacion contraseñas
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. LOCAL
        $local = new Local();
        $local->setNombre('Peluquería Venus - Alcalá la Real');
        $local->setDireccion('Calle Ecuador, 21');
        $local->setCiudad('Alcalá la Real');
        $manager->persist($local);

        // 2. SERVICIOS
        $servicios = [
            ['Corte Caballero', 30, 15.50, 'Peluquería'],
            ['Corte Señora', 60, 25.00, 'Peluquería'],
            ['Tinte y Color', 90, 45.00, 'Coloración'],
            ['Peinado Gala', 45, 20.00, 'Estética'],
            ['Lavado y Masaje', 15, 8.00, 'Bienestar']
        ];

        foreach ($servicios as $datos) {
            $servicio = new Servicio();
            $servicio->setNombre($datos[0]);
            $servicio->setDuration($datos[1]);
            $servicio->setPrecio($datos[2]);
            $servicio->setCategoria($datos[3]);
            $servicio->setActivo(true);

            //  Vinculamos el servicio al local
            $servicio->setLocal($local);

            $manager->persist($servicio);
        }

        // 3. PRODUCTOS 
        $productos = [
            ['Champú Color Vive', 'L\'Oréal', 'Protege el color de tu cabello teñido.', 12.50, 'champu.jpg'],
            ['Mascarilla Nutritiva', 'Kérastase', 'Hidratación profunda para pelo seco.', 28.00, 'mascarilla.jpg'],
            ['Cera Fijación Fuerte', 'American Crew', 'Acabado mate y fijación duradera.', 15.00, 'cera.jpg']
        ];

        foreach ($productos as $datos) {
            $producto = new Producto();
            $producto->setNombre($datos[0]);
            $producto->setMarca($datos[1]);
            $producto->setDescripcion($datos[2]);
            $producto->setPrecio($datos[3]);
            $producto->setImagen($datos[4]);

            //  Vinculamos el producto al local
            $producto->setLocal($local);

            $manager->persist($producto);
        }

        // 4. HORARIOS 
        $diasPrueba = [
            '2026-02-16 09:00:00', // Un Lunes a las 9:00
            '2026-02-17 10:00:00', // Un Martes a las 10:00
            '2026-02-18 09:30:00', // Un Miércoles a las 9:30
        ];

        foreach ($diasPrueba as $fechaHora) {
            $horario = new Horario();
            $horario->setHoraApertura(new \DateTime($fechaHora));
            // Suponemos que el local cierra 8 horas después de abrir
            $horario->setHoraCierre((new \DateTime($fechaHora))->modify('+8 hours'));

            // Vinculamos el horario al local
            $horario->setLocal($local);

            $manager->persist($horario);
        }

        // 5. CREAR UN USUARIO (ADMIN)

        $admin = new User();
        $admin->setEmail('admin@admin.com');
        $admin->setNombre('Merce');
        $admin->setRoles(['ROLE_ADMIN']);

        // Encriptamos la contraseña "admin123"
        $hashedPassword = $this->hasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        // 6. CREAR CLIENTE
        $client = new User();
        $client->setEmail('cliente@gmail.com');
        $client->setNombre('Antonio');
        $client->setRoles(['ROLE_USER']);
        $hashedPassword = $this->hasher->hashPassword($client, 'cliente123');
        $client->setPassword($hashedPassword);
        $manager->persist($client);

        // 7. GUARDAR TODO EN BASE DE DATOS
        $manager->flush();
    }
}