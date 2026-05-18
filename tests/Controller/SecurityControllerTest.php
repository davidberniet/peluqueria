<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginDevuelveOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginTieneFormulario(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        // Verificamos que hay un formulario de login
        $this->assertSelectorExists('form');
        // El campo puede llamarse _username o email según la config del security.yaml
        $this->assertTrue(
            $crawler->filter('input[name="_username"]')->count() > 0
            || $crawler->filter('input[name="email"]')->count() > 0,
            'El formulario de login debe tener un campo de usuario/email'
        );
    }

    public function testPerfilSinLoginRedirigeLlogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/perfil');

        // Symfony redirige a login cuando no está autenticado
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    public function testAdminSinLoginRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/dashboard');

        $this->assertResponseRedirects();
    }
}
