<?php

namespace App\Tests\Entity;

use App\Entity\Cita;
use App\Entity\Servicio;
use App\Entity\User;
use App\Entity\Local;
use PHPUnit\Framework\TestCase;

class CitaTest extends TestCase
{
    private Cita $cita;

    protected function setUp(): void
    {
        $this->cita = new Cita();
    }

    public function testEstadoInicial(): void
    {
        // Una cita recién creada no tiene estado asignado
        $this->assertNull($this->cita->getEstado());
    }

    public function testSetYGetEstado(): void
    {
        $this->cita->setEstado('Pendiente');
        $this->assertSame('Pendiente', $this->cita->getEstado());

        $this->cita->setEstado('Confirmada');
        $this->assertSame('Confirmada', $this->cita->getEstado());

        $this->cita->setEstado('Cancelada');
        $this->assertSame('Cancelada', $this->cita->getEstado());
    }

    public function testFechaInicio(): void
    {
        $fecha = new \DateTime('2025-09-15 10:00:00');
        $this->cita->setFechaInicio($fecha);

        $this->assertSame($fecha, $this->cita->getFechaInicio());
        $this->assertSame('2025-09-15', $this->cita->getFechaInicio()->format('Y-m-d'));
        $this->assertSame('10:00', $this->cita->getFechaInicio()->format('H:i'));
    }

    public function testFechaFin(): void
    {
        $inicio = new \DateTime('2025-09-15 10:00:00');
        $fin    = clone $inicio;
        $fin->modify('+30 minutes');

        $this->cita->setFechaInicio($inicio);
        $this->cita->setFechaFin($fin);

        $this->assertSame('10:30', $this->cita->getFechaFin()->format('H:i'));
    }

    public function testFechaFinEsMasTardeQueFechaInicio(): void
    {
        $inicio = new \DateTime('2025-09-15 10:00:00');
        $fin    = clone $inicio;
        $fin->modify('+60 minutes');

        $this->cita->setFechaInicio($inicio);
        $this->cita->setFechaFin($fin);

        $this->assertGreaterThan(
            $this->cita->getFechaInicio()->getTimestamp(),
            $this->cita->getFechaFin()->getTimestamp(),
            'La fecha de fin debe ser posterior a la de inicio'
        );
    }

    public function testNotas(): void
    {
        $this->assertNull($this->cita->getNotas());

        $this->cita->setNotas('Quiero el flequillo recto');
        $this->assertSame('Quiero el flequillo recto', $this->cita->getNotas());

        $this->cita->setNotas(null);
        $this->assertNull($this->cita->getNotas());
    }

    public function testAsignarUsuario(): void
    {
        $user = new User();
        $this->cita->setUsuario($user);

        $this->assertSame($user, $this->cita->getUsuario());
    }

    public function testAsignarLocal(): void
    {
        $local = new Local();
        $this->cita->setLocal($local);

        $this->assertSame($local, $this->cita->getLocal());
    }

    public function testAsignarEmpleado(): void
    {
        $empleado = new User();
        $this->cita->setEmpleado($empleado);

        $this->assertSame($empleado, $this->cita->getEmpleado());
    }

    public function testColeccionServiciosInicialmenteVacia(): void
    {
        $this->assertCount(0, $this->cita->getServicios());
    }

    public function testAddYRemoveServicio(): void
    {
        $servicio = new Servicio();
        $servicio->setNombre('Corte de pelo');
        $servicio->setPrecio(15.00);
        $servicio->setDuration(30);
        $servicio->setCategoria('Corte');

        $this->cita->addServicio($servicio);
        $this->assertCount(1, $this->cita->getServicios());
        $this->assertTrue($this->cita->getServicios()->contains($servicio));

        // Añadir el mismo servicio no duplica
        $this->cita->addServicio($servicio);
        $this->assertCount(1, $this->cita->getServicios());

        $this->cita->removeServicio($servicio);
        $this->assertCount(0, $this->cita->getServicios());
    }

    public function testColeccionProductosInicialmenteVacia(): void
    {
        $this->assertCount(0, $this->cita->getProductos());
    }

    public function testIdInicialmenteNull(): void
    {
        $this->assertNull($this->cita->getId());
    }
}
