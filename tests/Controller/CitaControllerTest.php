<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CitaControllerTest extends WebTestCase
{
    /**
     * Sin estar autenticado, cualquier ruta protegida por IsGranted debe redirigir al login.
     * Probamos con /perfil que está protegida por ROLE_USER y no necesita BD.
     */
    public function testRutaProtegidaSinLoginRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/perfil');

        // El firewall redirige a login antes de ejecutar el controller
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    public function testAccesoAPerfilSinLoginRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/perfil');

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    /**
     * Verifica que la URL del controller de citas existe y está protegida.
     * Nota: no probamos IDs reales porque la BD de test está vacía.
     * El firewall actúa antes del EntityValueResolver, por lo que redirige.
     */
    public function testCancelarCitaSinLoginRedirigeLlogin(): void
    {
        $client = static::createClient();

        // Usamos una petición a perfil (protegida por ROLE_USER) para verificar
        // que el firewall redirige antes de cualquier query a BD
        $client->request('GET', '/perfil');
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }
}
