<?php

namespace App\DataFixtures;

use App\Entity\Servicio;
use App\Entity\Local;
use App\Entity\Producto;
use App\Entity\Horario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
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
            
            // Vinculamos el horario al local
            $horario->setLocal($local); 
            
            $manager->persist($horario);
        }

        // 5. GUARDAR TODO EN BASE DE DATOS
        $manager->flush();
    }
}