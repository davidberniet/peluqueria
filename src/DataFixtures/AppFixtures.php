<?php

namespace App\DataFixtures;

use App\Entity\Servicio;
use App\Entity\Local;
use App\Entity\Producto;
use App\Entity\Horario;
use App\Entity\User;
use App\Entity\Cita;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. LOCALES
        $local1 = new Local();
        $local1->setNombre('Venus Alcalá');
        $local1->setDireccion('C/ Ecuador 21');
        $local1->setCiudad('Alcalá la Real');
        $local1->setTelefono('953 11 22 33');
        $local1->setEmail('alcala@venus.com');
        $local1->setActivo(true);
        $manager->persist($local1);

        $local2 = new Local();
        $local2->setNombre('Venus Arabial');
        $local2->setDireccion('C/ Arabial 110');
        $local2->setCiudad('Granada');
        $local2->setTelefono('958 11 22 33');
        $local2->setEmail('arabial@venus.com');
        $local2->setActivo(true);
        $manager->persist($local2);

        // 2. SERVICIOS
        $serviciosLocal1 = [
            ['Corte Señora', 60, 25.00, 'Peluquería'],
            ['Peinado Gala', 45, 20.00, 'Estética'],
            ['Tratamiento Capilar', 60, 40.00, 'Bienestar'],
            ['Corte Caballero', 30, 15.50, 'Peluquería'],
            ['Coloración', 120, 60.00, 'Coloración']
        ];

        $serviciosLocal2 = [
            ['Corte Caballero', 30, 15.50, 'Peluquería'],
            ['Mascarilla Nutritiva', 30, 12.00, 'Bienestar'],
            ['Lavado y Masaje', 15, 8.00, 'Bienestar'],
        ];

        $serviciosEntidades = [];

        foreach ($serviciosLocal1 as $datos) {
            $servicio = new Servicio();
            $servicio->setNombre($datos[0]);
            $servicio->setDuration($datos[1]);
            $servicio->setPrecio($datos[2]);
            $servicio->setCategoria($datos[3]);
            $servicio->setActivo(true);
            $servicio->setLocal($local1);
            $manager->persist($servicio);
            $serviciosEntidades[] = $servicio;
        }

        foreach ($serviciosLocal2 as $datos) {
            $servicio = new Servicio();
            $servicio->setNombre($datos[0]);
            $servicio->setDuration($datos[1]);
            $servicio->setPrecio($datos[2]);
            $servicio->setCategoria($datos[3]);
            $servicio->setActivo(true);
            $servicio->setLocal($local2);
            $manager->persist($servicio);
            $serviciosEntidades[] = $servicio;
        }

        // 3. PRODUCTOS 
        $productos = [
            ['Champú Color Vive', 'L\'Oréal', 'Protege el color de tu cabello teñido.', 12.50, 'champu.jpg', $local1],
            ['Mascarilla Nutritiva', 'Kérastase', 'Hidratación profunda para pelo seco.', 28.00, 'mascarilla.jpg', $local2],
            ['Cera Fijación Fuerte', 'American Crew', 'Acabado mate y fijación duradera.', 15.00, 'cera.jpg', $local1]
        ];

        $productosEntidades = [];
        foreach ($productos as $datos) {
            $producto = new Producto();
            $producto->setNombre($datos[0]);
            $producto->setMarca($datos[1]);
            $producto->setDescripcion($datos[2]);
            $producto->setPrecio($datos[3]);
            $producto->setImagen($datos[4]);
            $producto->addLocale($datos[5]); // ManyToMany → addLocale(), no setLocal()
            $producto->setStock(10);
            $manager->persist($producto);
            $productosEntidades[] = $producto;
        }

        // 4. HORARIOS 
        // Local 1: 09:00–14:00 y 16:00–20:00
        $horario1 = new Horario();
        $horario1->setHoraApertura(new \DateTime('09:00:00'));
        $horario1->setHoraCierre(new \DateTime('14:00:00'));
        $horario1->setIntervaloMinutos(30);
        $horario1->setLocal($local1);
        $manager->persist($horario1);

        $horario2 = new Horario();
        $horario2->setHoraApertura(new \DateTime('16:00:00'));
        $horario2->setHoraCierre(new \DateTime('20:00:00'));
        $horario2->setIntervaloMinutos(30);
        $horario2->setLocal($local1);
        $manager->persist($horario2);

        // Local 2: 10:00–19:00
        $horario3 = new Horario();
        $horario3->setHoraApertura(new \DateTime('10:00:00'));
        $horario3->setHoraCierre(new \DateTime('19:00:00'));
        $horario3->setIntervaloMinutos(30);
        $horario3->setLocal($local2);
        $manager->persist($horario3);


        // 5. USUARIOS (Empleados y Clientes)
        
        // Empleados Local 1
        $merce = new User();
        $merce->setEmail('merce@venus.com');
        $merce->setNombre('Merce');
        $merce->setRoles(['ROLE_ADMIN', 'ROLE_EMPLEADO']);
        $merce->setLocal($local1);
        $merce->setPassword($this->hasher->hashPassword($merce, 'venus123'));
        $manager->persist($merce);

        $laura = new User();
        $laura->setEmail('laura@venus.com');
        $laura->setNombre('Laura');
        $laura->setRoles(['ROLE_EMPLEADO']);
        $laura->setLocal($local1);
        $laura->setPassword($this->hasher->hashPassword($laura, 'venus123'));
        $manager->persist($laura);

        // Empleados Local 2
        $carlos = new User();
        $carlos->setEmail('carlos@venus.com');
        $carlos->setNombre('Carlos');
        $carlos->setRoles(['ROLE_EMPLEADO']);
        $carlos->setLocal($local2);
        $carlos->setPassword($this->hasher->hashPassword($carlos, 'venus123'));
        $manager->persist($carlos);

        $ana = new User();
        $ana->setEmail('ana@venus.com');
        $ana->setNombre('Ana');
        $ana->setRoles(['ROLE_EMPLEADO']);
        $ana->setLocal($local2);
        $ana->setPassword($this->hasher->hashPassword($ana, 'venus123'));
        $manager->persist($ana);

        // Clientes
        $clientes = [];
        $nombresClientes = ['Antonio', 'Sara', 'Pedro'];
        foreach ($nombresClientes as $nombre) {
            $cliente = new User();
            $cliente->setEmail(strtolower($nombre) . '@gmail.com');
            $cliente->setNombre($nombre);
            $cliente->setRoles(['ROLE_USER']);
            $cliente->setPassword($this->hasher->hashPassword($cliente, 'cliente123'));
            $manager->persist($cliente);
            $clientes[] = $cliente;
        }

        // 6. CITAS DE EJEMPLO
        
        // Cita 1: Antonio con Merce en Local 1
        $cita1 = new Cita();
        $cita1->setUsuario($clientes[0]); // Antonio
        $cita1->setLocal($local1);
        $cita1->setEmpleado($merce);
        $cita1->setEstado('Confirmada');
        $cita1->addServicio($serviciosEntidades[0]); // Corte Caballero
        $cita1->addProducto($productosEntidades[0]); // Champú Color Vive
        $fechaCita1 = new \DateTime('tomorrow 10:00:00');
        $cita1->setFechaInicio($fechaCita1);
        $cita1->setFechaFin((clone $fechaCita1)->modify('+30 minutes'));
        $manager->persist($cita1);

        // Cita 2: Sara con Carlos en Local 2
        $cita2 = new Cita();
        $cita2->setUsuario($clientes[1]); // Sara
        $cita2->setLocal($local2);
        $cita2->setEmpleado($carlos);
        $cita2->setEstado('Pendiente');
        $cita2->addServicio($serviciosEntidades[5]); // Mascarilla Nutritiva (aprox)
        $fechaCita2 = new \DateTime('tomorrow 11:30:00');
        $cita2->setFechaInicio($fechaCita2);
        $cita2->setFechaFin((clone $fechaCita2)->modify('+30 minutes'));
        $manager->persist($cita2);

        // Cita 3: Pedro sin preferencia en Local 1
        $cita3 = new Cita();
        $cita3->setUsuario($clientes[2]); // Pedro
        $cita3->setLocal($local1);
        $cita3->setEstado('Confirmada');
        $cita3->addServicio($serviciosEntidades[0]); // Corte Caballero
        $fechaCita3 = new \DateTime('tomorrow 17:00:00');
        $cita3->setFechaInicio($fechaCita3);
        $cita3->setFechaFin((clone $fechaCita3)->modify('+30 minutes'));
        $manager->persist($cita3);

        // 7. CITAS PASADAS CON VALORACIÓN (aparecen en la home)
        $resenas = [
            [
                'cliente'    => $clientes[0], // Antonio
                'local'      => $local1,
                'empleado'   => $merce,
                'servicio'   => $serviciosEntidades[0], // Corte Señora
                'fecha'      => '-20 days 11:00:00',
                'duracion'   => 60,
                'valoracion' => 5,
                'comentario' => 'Reservé por la web en menos de un minuto. Me atendieron genial y salí con un corte espectacular. Totalmente recomendado.',
            ],
            [
                'cliente'    => $clientes[1], // Sara
                'local'      => $local1,
                'empleado'   => $laura,
                'servicio'   => $serviciosEntidades[4], // Coloración
                'fecha'      => '-12 days 10:00:00',
                'duracion'   => 120,
                'valoracion' => 5,
                'comentario' => 'La coloración me quedó preciosa. Los productos que usan son de altísima calidad y el resultado dura muchísimo. ¡Volvería mil veces!',
            ],
            [
                'cliente'    => $clientes[2], // Pedro
                'local'      => $local2,
                'empleado'   => $carlos,
                'servicio'   => $serviciosEntidades[5], // Corte Caballero local2
                'fecha'      => '-5 days 17:00:00',
                'duracion'   => 30,
                'valoracion' => 4,
                'comentario' => 'Genial poder ver la disponibilidad en tiempo real y no tener que llamar. La atención es 10/10 y el ambiente del salón es muy acogedor.',
            ],
        ];

        foreach ($resenas as $datos) {
            $cita = new Cita();
            $cita->setUsuario($datos['cliente']);
            $cita->setLocal($datos['local']);
            $cita->setEmpleado($datos['empleado']);
            $cita->setEstado('Confirmada');
            $cita->addServicio($datos['servicio']);
            $fecha = new \DateTime($datos['fecha']);
            $cita->setFechaInicio($fecha);
            $cita->setFechaFin((clone $fecha)->modify('+' . $datos['duracion'] . ' minutes'));
            $cita->setValoracion($datos['valoracion']);
            $cita->setComentarioValoracion($datos['comentario']);
            $manager->persist($cita);
        }

        $manager->flush();
    }
}