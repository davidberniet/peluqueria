<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testHomeDevuelveOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testServiciosDevuelveOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/servicios');

        $this->assertResponseIsSuccessful();
    }

    public function testContactoDevuelveOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contacto');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testHomeTieneEnlaceAServicios(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/servicios"]');
    }

    public function testNavContieneLinkContacto(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/contacto"]');
    }
}
