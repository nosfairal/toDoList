<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test Display login page
     */
    public function testDisplayLoginPage()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test Display the login form
     */
    public function testDisplayLoginForm(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
    
    /**
     * Test Login succes for user
     */
    public function testAuthenticationSuccess()
    {
        //$user = new User;
        //$token = new UsernamePasswordToken($user, null, 'main', []);
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'user1',
            'password' => 'user1'
            //'_csrf_token' => $token
        ]);
        //$token = new UsernamePasswordToken($user, null, 'main', []);
        //$this->client->getContainer()->get('security.token_storage')->setToken($token);
        $this->client->submit($form);
        //$this->client->getContainer()->get('security.token_storage')->setToken($token);

        //$this->assertTrue($this->client->getResponse()->isRedirection());

        //$this->assertResponseRedirects('/');
        //$crawler = $this->client->followRedirect();
        //$this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        //$this->assertResponseRedirects("/", Response::HTTP_FOUND);
        //$this->client->followRedirect();
        $this->assertSelectorExists('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
        //$this->assertSelectorExists('.btn-success', "Se connecter");
        //static::assertSame("Invalid credentials.", $crawler->filter('div.alert.alert-danger')->text());

    }
    /*public function testLoginSuccess()
    {
        $client = static::createClient();

        $crawler = $this->client->request('GET', '/login');

        static::assertSame(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton("Se connecter")->form([
            "username" => "test",
            "password" => 'Test123'
        ]);

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        static::assertSame(200, $this->client->getResponse()->getStatusCode());*/

        //Pour utiliser $client dans d'autres méthodes
        /*return $client;
    }*/

    /**
     * Test Login success as admin
     */
    public function testLoginSuccessAsAdmin()
    {

        $crawler = $this->client->request('GET', '/login');
        //$this->client->request('POST', '/login', ['username' => 'user1', 'password' => 'user1']);
        static::assertSame(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton("Se connecter")->form([
            "username" => "admin",
            "password" => 'admin'
        ]);

        $this->client->submit($form);
        //$crawler = $this->client->followRedirect();

        //static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
        //static::assertSame("Invalid credentials.", $crawler->filter('div.alert.alert-danger')->text());
    }

    /**
     * Test login fail
     */
    public function testLoginFail()
    {

        $crawler = $this->client->request('GET', '/login');

        static::assertSame(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton("Se connecter")->form([
            "username" => "TestUsername",
            "password" => 'test'
        ]);

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        static::assertSame(200, $this->client->getResponse()->getStatusCode());

        static::assertSame("Invalid credentials.", $crawler->filter('div.alert.alert-danger')->text());
    }

    /**
     * Test Logout
     */
    public function testLogout()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('user1@hotmail.com');

        // simulate $testUser being logged in
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/logout');

        $this->client->followRedirect();

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);

        $this->client->followRedirect();
        $this->assertSelectorExists('label', 'Mot de passe');
        //$this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // $this->assertResponseIsSuccessful()
        // $this->assertResponseRedirects("/", Response::HTTP_OK);
    }

    /**
     * Test Logout with button
     */
    public function testLogoutWithButton()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('user1@hotmail.com');

        // simulate $testUser being logged in
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/');

        // click on "Se déconnecter" button
        $link = $crawler->selectLink('Se déconnecter')->link();
        $crawler = $this->client->click($link);

        $this->client->followRedirect();

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
    }
}