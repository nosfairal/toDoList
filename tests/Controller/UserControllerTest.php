<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /**
     * constant represent a email with role is USER
     */
    protected const EMAIL_USER = 'test@test.fr';

    /**
     * constant represents the email of a user used for tests
     */
    protected const EMAIL = 'test2@test.fr';

    /**
     * constant represent a email with role is ADMIN
     */
    protected const EMAIL_ADMIN = 'admin@admin.com';

    /**
     * constant represent an user's id
     */
    protected const USER_ID = 3;

    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test Display users list without logged
     */
    public function testListUsersNotlogged(): void
    {
        $this->client->request('GET', '/users');
        //$this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('label', 'Mot de passe');
    }

    /**
     * Test Display users list logged as user
     */
    public function testListUsersWithRoleUser()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER);
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        //$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->followRedirect();
        // $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertSelectorExists('.alert.alert-danger', "VOUS AVEZ ETE REDIRIGE SUR CETTE PAGE CAR : N'étant pas administrateur de ce site vous n'avez pas accès à la ressource que vous avez demandé.");
    }

    /**
     * Test Display users list logged as admin
     */
    public function testListUsersWithRoleAdmin()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_ADMIN);
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h1', 'Liste des utilisateurs');
    }

    /**
     * Test Create an User logged as user
     */
    public function testCreateUserWithRoleUser()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER);
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        // $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->followRedirect();
        // $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertSelectorExists('.alert.alert-danger', "VOUS AVEZ ETE REDIRIGE SUR CETTE PAGE CAR : N'étant pas administrateur de ce site vous n'avez pas accès à la ressource que vous avez demandé");
    }

    /**
     * Test Create an User logged as admin
     */
    public function testCreateUserWithRoleADMIN()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_ADMIN);
        $this->client->loginUser($testUser);

        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'testcreateUser';
        $form['user[password][first]'] = 'testcreateUserpassword';
        $form['user[password][second]'] = 'testcreateUserpassword';
        $form['user[email]'] = 'testcreateUser@hotmail.com';
        // $form['user[roles]'] = 'ROLE_ADMIN'; //taditionnelle
        $form['user[roles]']->select('ROLE_ADMIN');  //pour les select ou les choices(dans mon cas)
        // $form['user[roles]']->tick();    //que pour les checkBox moi ces t un choice

        $crawler = $this->client->submit($form);

        // $this->assertResponseRedirects();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! L'utilisateur a bien été ajouté. ");

        $this->assertNotNull($userRepository->findOneBy(['email' => 'testcreateUser@hotmail.com']));
    }

    /**
     * Test Update an User logged as user
     */
    public function testEditUserWithRoleUSER()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER);
        $this->client->loginUser($testUser);

        $userEdit = $userRepository->findOneBy(['email' => self::EMAIL]);
        $userEdit_id = $userEdit->getId();

        $this->client->request('GET', "/users/$userEdit_id/edit");
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        // $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->followRedirect();
        // $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertSelectorExists('.alert.alert-danger', "VOUS AVEZ ETE REDIRIGE SUR CETTE PAGE CAR : N'étant pas administrateur de ce site vous n'avez pas accès à la ressource que vous avez demandé");
    }

    /**
     * Test Update an User logged as admin
     */
    public function testEditUserWithRoleADMIN()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_ADMIN);
        $this->client->loginUser($testUser);

        $userEdit = $userRepository->findOneBy(['email' => self::EMAIL]);
        $userEdit_id = $userEdit->getId();


        $this->client->followRedirects();

        $crawler = $this->client->request('GET', "/users/$userEdit_id/edit");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[password][first]'] = 'Test234';
        $form['user[password][second]'] = 'Test234';

        $crawler = $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! Le compte utilisateur a bien été modifié");
    }

    /**
     * Test Delete an User logged as admin
     */
    public function testDeleteUserWithRoleADMIN()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testAdmin = $userRepository->findOneByEmail(self::EMAIL_ADMIN);

        $this->client->loginUser($testAdmin);

        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/users/' . self::USER_ID . '/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success', "L'utilisateur a bien été supprimé.");
        $this->assertNull($userRepository->find(self::USER_ID));
    }
}