<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testDisplayLoginPage()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    public function testDisplayLoginForm(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
    public function testAuthentificationSuccess()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'admin',
            '_password' => 'admin'
        ]);
        $this->client->submit($form);

        // $this->assertTrue($this->client->getResponse()->isRedirection());

        // $this->assertResponseRedirects('/');
        // $this->client->followRedirect();

        $this->assertSelectorExists('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");

        // echo $this->client->getResponse()->getContent();
        // var_dump($this->client->getResponse()->getContent());
    }
    /*public function testLoginSuccess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        static::assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton("Se connecter")->form([
            "_username" => "test",
            "_password" => 'Test123'
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        static::assertSame(200, $client->getResponse()->getStatusCode());*/

        //Pour utiliser $client dans d'autres méthodes
        /*return $client;
    }

    public function testLoginSuccessAsAdmin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        static::assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton("Se connecter")->form([
            "_username" => "admin",
            "_password" => 'admin'
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        static::assertSame(200, $client->getResponse()->getStatusCode());*/

        //Pour utiliser $client dans d'autres méthodes
        /*return $client;
    }


    public function testLoginFailed()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        static::assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton("Se connecter")->form([
            "_username" => "TestUsername",
            "_password" => 'test'
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        static::assertSame(200, $client->getResponse()->getStatusCode());

        static::assertSame("Invalid credentials.", $crawler->filter('div.alert.alert-danger')->text());
    }*/
}