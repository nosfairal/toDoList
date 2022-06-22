<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
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
            '_username' => 'test',
            '_password' => 'Test123'
        ]);
        $this->client->submit($form);

        //$this->assertTrue($this->client->getResponse()->isRedirection());

        //$this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        //$this->assertResponseRedirects("/", Response::HTTP_OK);
        //$this->assertSelectorExists('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
        //$this->assertSelectorExists('.btn-danger', "Se déconnecter");

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

    /**
     * Test Logout
     */
    public function testLogout()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('test@test.fr');

        // simulate $testUser being logged in
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/logout');

        $this->client->followRedirect();

        //$this->assertResponseRedirects("/login", Response::HTTP_FOUND);

        $this->client->followRedirect();
        $this->assertSelectorExists('label', 'Mot de passe');
        //$this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // $this->assertResponseIsSuccessful()
        // $this->assertResponseRedirects("/", Response::HTTP_OK);
    }
}