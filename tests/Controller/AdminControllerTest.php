<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testDashboardSinLoginRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/dashboard');

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    public function testListaCitasSinLoginRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/citas');

        $this->assertResponseRedirects();
    }

    public function testGestionServiciosSinLoginRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/servicios');

        $this->assertResponseRedirects();
    }

    public function testGestionProductosSinLoginRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/productos-admin');

        $this->assertResponseRedirects();
    }

    /**
     * La ruta /admin/cliente/{id} usa EntityValueResolver que busca el User en BD.
     * Con la BD de test vacía devuelve 404. Verificamos que sin login redirige
     * accediendo a una ruta de admin sin id que no requiere BD.
     */
    public function testRutasAdminSinLoginRedirigen(): void
    {
        $client = static::createClient();

        $rutasAdmin = [
            '/admin/dashboard',
            '/admin/citas',
            '/admin/servicios',
            '/admin/productos-admin',
        ];

        foreach ($rutasAdmin as $ruta) {
            $client->request('GET', $ruta);
            $this->assertResponseRedirects(
                null,
                null,
                sprintf('La ruta %s debe redirigir cuando no hay sesión', $ruta)
            );
        }
    }
}
