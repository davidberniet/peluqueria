<?php

namespace App\Tests\Entity;

use App\Entity\Servicio;
use PHPUnit\Framework\TestCase;

class ServicioTest extends TestCase
{
    private Servicio $servicio;

    protected function setUp(): void
    {
        $this->servicio = new Servicio();
    }

    public function testIdInicialmenteNull(): void
    {
        $this->assertNull($this->servicio->getId());
    }

    public function testNombre(): void
    {
        $this->servicio->setNombre('Corte de pelo');
        $this->assertSame('Corte de pelo', $this->servicio->getNombre());
    }

    public function testPrecio(): void
    {
        $this->servicio->setPrecio(18.50);
        $this->assertSame(18.50, $this->servicio->getPrecio());
    }

    public function testPrecioCero(): void
    {
        $this->servicio->setPrecio(0.0);
        $this->assertSame(0.0, $this->servicio->getPrecio());
    }

    public function testDuration(): void
    {
        $this->servicio->setDuration(45);
        $this->assertSame(45, $this->servicio->getDuration());
    }

    public function testCategoria(): void
    {
        $this->servicio->setCategoria('Coloración');
        $this->assertSame('Coloración', $this->servicio->getCategoria());
    }

    public function testActivoPorDefecto(): void
    {
        // El campo tiene valor por defecto true en la entidad
        $this->assertTrue($this->servicio->isActivo());
    }

    public function testSetActivo(): void
    {
        $this->servicio->setActivo(false);
        $this->assertFalse($this->servicio->isActivo());

        $this->servicio->setActivo(true);
        $this->assertTrue($this->servicio->isActivo());
    }

    public function testServicioCompletoConsistente(): void
    {
        $this->servicio->setNombre('Tinte completo');
        $this->servicio->setPrecio(45.00);
        $this->servicio->setDuration(90);
        $this->servicio->setCategoria('Coloración');
        $this->servicio->setActivo(true);

        $this->assertSame('Tinte completo', $this->servicio->getNombre());
        $this->assertSame(45.00, $this->servicio->getPrecio());
        $this->assertSame(90, $this->servicio->getDuration());
        $this->assertSame('Coloración', $this->servicio->getCategoria());
        $this->assertTrue($this->servicio->isActivo());
    }
}
