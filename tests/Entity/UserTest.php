<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;


class UserTest extends KernelTestCase
{
    private const EMAIL_CONSTRAINT_MESSAGE = "Veuillez entrer une adresse email valide.";

    private const NOT_BLANK_CONSTRAINT_MESSAGE = 'Ce champ est requis !';

    private const INVALID_EMAIL_VALUE = "toto@gmail";

    private const VALID_EMAIL_VALUE = "toto@gmail.com";

    private const PASSWORD_REGEX_CONSTRAINT_MESSAGE = "Le mot de passe doit contenir au moins une minuscule et une majuscule !";

    private const PASSWORD_LENGTH_CONSTRAINT_MESSAGE = "Votre mot de passe doit contenir au moins 6 caractères.";

    private const VALID_PASSWORD_VALUE = "Toto25";

    private const INVALID_PASSWORD_VALUE = "toto25";

    private const INVALID_PASSWORD_LENGTH_VALUE = "Toto";

    private const VALID_USERNAME_VALUE = "toto";

    private const ROLES_VALUE = ["ROLE_USER"];


    protected function setUp(): void
    {
        static::bootKernel();
        $container = self::$kernel->getContainer()->get('test.service_container');
        $this->validator = $container->get('validator');
    }

    /**
     * Test User Valid
     * 
     */
    public function testUserIsValid(): void
    {
        $user = new User();

        //Verify setters
        $user->setEmail(self::VALID_EMAIL_VALUE)
            ->setPassword(self::VALID_PASSWORD_VALUE)
            ->setUsername(self::VALID_USERNAME_VALUE)
            ->setRoles(self::ROLES_VALUE);

        //Verify getters
        $this->assertEquals(self::VALID_USERNAME_VALUE, $user->getUsername());
        $this->assertEquals(self::VALID_PASSWORD_VALUE, $user->getPassword());
        $this->assertEquals(self::VALID_EMAIL_VALUE, $user->getEmail());
        $this->assertEquals(self::ROLES_VALUE, $user->getRoles());
        $this->assertEquals(null, $user->getId());

        //Number of errors awaited = 0
        $this->getValidationErrors($user, 0);
    }

    /**
     * Test User Invalid because no Email
     * 
     */
    public function testUserIsInvalidBecauseNoEmail(): void
    {
        $user = new User();

        //Verify setters
        $user->setPassword(self::VALID_PASSWORD_VALUE)
            ->setUsername(self::VALID_USERNAME_VALUE)
            ->setRoles(self::ROLES_VALUE);

        //verify getters
        $this->assertEquals(self::VALID_USERNAME_VALUE, $user->getUserIdentifier());
        $this->assertEquals(self::VALID_PASSWORD_VALUE, $user->getPassword());
        $this->assertEquals(self::ROLES_VALUE, $user->getRoles());

        //Number of errors awaited = 1
        $errors = $this->getValidationErrors($user, 1);

        //Return of message = message assert from Entity
        $this->assertEquals(self::NOT_BLANK_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }

    /**
     * Test User Invalid because no Password
     * 
     */
    public function testUserIsInvalidBecauseNoPassword(): void
    {
        $user = new User();

        //Verify setters
        $user->setEmail(self::VALID_EMAIL_VALUE)
            ->setUsername(self::VALID_USERNAME_VALUE)
            ->setRoles(self::ROLES_VALUE);

        //verify getters
        $this->assertEquals(self::VALID_USERNAME_VALUE, $user->getUserIdentifier());
        $this->assertEquals(self::VALID_EMAIL_VALUE, $user->getEmail());
        $this->assertEquals(self::ROLES_VALUE, $user->getRoles());

        //Number of errors awaited = 1
        $errors = $this->getValidationErrors($user, 1);

        //Return of message = message assert from Entity
        $this->assertEquals(self::NOT_BLANK_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }

    /**
     * Test User Invalid because no Username
     * 
     */
    public function testUserIsInvalidBecauseNoUsername(): void
    {
        $user = new User();

        $user->setEmail(self::VALID_EMAIL_VALUE)
            ->setPassword(self::VALID_PASSWORD_VALUE)
            ->setRoles(self::ROLES_VALUE);

        //Verify getters
        $this->assertEquals(self::VALID_PASSWORD_VALUE, $user->getPassword());
        $this->assertEquals(self::VALID_EMAIL_VALUE, $user->getEmail());
        $this->assertEquals(self::ROLES_VALUE, $user->getRoles());

        //Number of errors awaited = 1
        $errors = $this->getValidationErrors($user, 1);

        //Return of message = message assert from Entity
        $this->assertEquals(self::NOT_BLANK_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }

    /**
     * Test User Invalid because Email invalid
     * 
     */
    public function testUserIsInvalidBecauseEmailInvalid(): void
    {
        $user = new User();

        $user->setEmail(self::INVALID_EMAIL_VALUE)
            ->setPassword(self::VALID_PASSWORD_VALUE)
            ->setUsername(self::VALID_USERNAME_VALUE)
            ->setRoles(self::ROLES_VALUE);

        //Verify getters
        $this->assertEquals(self::VALID_PASSWORD_VALUE, $user->getPassword());
        $this->assertEquals(self::VALID_USERNAME_VALUE, $user->getUserIdentifier());
        $this->assertEquals(self::INVALID_EMAIL_VALUE, $user->getEmail());
        $this->assertEquals(self::ROLES_VALUE, $user->getRoles());

        //Number of errors awaited = 1
        $errors = $this->getValidationErrors($user, 1);

        //Return of message = message assert from Entity
        $this->assertEquals(self::EMAIL_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }
    /**
     * Test EraseCredentials
     */
    public function testEraseCredentials()
    {
        $user =new User;
        $this->assertEquals(null, $user->eraseCredentials());
       
    }

    /**
     * Test User Invalid because Password invalid
     * 
     */
    public function testUserIsInvalidBecausePasswordInvalid(): void
    {
        $user = new User();

        $user->setEmail(self::VALID_EMAIL_VALUE)
            ->setPassword(self::INVALID_PASSWORD_VALUE)
            ->setUsername(self::VALID_USERNAME_VALUE)
            ->setRoles(self::ROLES_VALUE);

        //Verify getters
        $this->assertEquals(self::INVALID_PASSWORD_VALUE, $user->getPassword());
        $this->assertEquals(self::VALID_USERNAME_VALUE, $user->getUserIdentifier());
        $this->assertEquals(self::VALID_EMAIL_VALUE, $user->getEmail());
        $this->assertEquals(self::ROLES_VALUE, $user->getRoles());

        //NUmber of errors awaited = 1
        $errors = $this->getValidationErrors($user, 1);

        //Return of message = message assert from Entity
        $this->assertEquals(self::PASSWORD_REGEX_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }

    /**
     * Test User Invalid because Password length invalid
     * 
     */
    public function testUserIsInvalidBecausePasswordLengthInvalid(): void
    {
        $user = new User();

        $user->setEmail(self::VALID_EMAIL_VALUE)
            ->setPassword(self::INVALID_PASSWORD_LENGTH_VALUE)
            ->setUsername(self::VALID_USERNAME_VALUE)
            ->setRoles(self::ROLES_VALUE);

        //Verify getters
        $this->assertEquals(self::INVALID_PASSWORD_LENGTH_VALUE, $user->getPassword());
        $this->assertEquals(self::VALID_USERNAME_VALUE, $user->getUserIdentifier());
        $this->assertEquals(self::VALID_EMAIL_VALUE, $user->getEmail());
        $this->assertEquals(self::ROLES_VALUE, $user->getRoles());

        //NUmber of errors awaited = 1
        $errors = $this->getValidationErrors($user, 1);

        //Return of message = message assert from Entity
        $this->assertEquals(self::PASSWORD_LENGTH_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }

    /**
     * Test Relation
     * 
     */
    public function testTasks(): void
    {
        $this->user = new User();
        $this->task = new Task();

        $tasks = $this->user->getTasks($this->task->getAuthor());
        $this->assertSame($this->user->getTasks(), $tasks);

        $this->user->addtask($this->task);
        $this->assertCount(1, $this->user->getTasks());

        $this->user->removeTask($this->task);
        $this->assertCount(0, $this->user->getTasks());
    }

    /**
     * Test Salt
     */
    public function testGetSalt()
    {
        $user = new User();
        $this->assertEquals(null, $user->getSalt());
    }

    /**
     * Management of errors
     * 
     */
    private function getValidationErrors(User $user, int $numberOfExpectedErrors): ConstraintViolationList
    {
        $errors = $this->validator->validate($user);

        $this->assertCount($numberOfExpectedErrors, $errors);

        return $errors;
    }
}