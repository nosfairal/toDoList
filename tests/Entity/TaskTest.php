<?php

namespace App\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Task;
use App\Entity\User;
use DateTime;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskTest extends KernelTestCase
{
    private const NOT_BLANK_CONSTRAINT_MESSAGE = "Ce champ est requis !";

    private const VALID_TITLE_VALUE = "Test";

    private const VALID_CONTENT_VALUE = "Contenu Test";

    private const VALID_IS_DONE_VALUE = false;

    protected function setUp(): void
    {
        static::bootKernel();
        $container = self::$kernel->getContainer()->get('test.service_container');
        $this->validator = $container->get('validator');
    }

    /**
     * Test Task Valid
     * 
     */
    public function testTaskIsValid()
    {
        $task = new Task();

        //Verify setters
        $task->setTitle(self::VALID_TITLE_VALUE)
            ->setContent(self::VALID_CONTENT_VALUE)
            ->setIsDone(self::VALID_IS_DONE_VALUE)
            ->setCreatedAt(new DateTime());

        //Verify getters
        $this->assertEquals(self::VALID_TITLE_VALUE, $task->getTitle());
        $this->assertEquals(self::VALID_CONTENT_VALUE, $task->getContent());
        $this->assertEquals(self::VALID_IS_DONE_VALUE, $task->getIsDone());
        $this->assertInstanceOf(DateTime::class, $task->getCreatedAt());
        $this->assertEquals(null, $task->getId());

        //Number of errors awaited = 0
        $this->getValidationErrors($task, 0);
    }

    /**
     * Test Task Invalid because no Title
     * 
     */
    public function testTaskIsInvalidBecauseNoTitle(): void
    {
        $task = new Task();

        //Verify setters
        $task->setContent(self::VALID_CONTENT_VALUE)
            ->setIsDone(self::VALID_IS_DONE_VALUE)
            ->setCreatedAt(new DateTime());

        //Verify getters
        $this->assertEquals(self::VALID_CONTENT_VALUE, $task->getContent());
        $this->assertEquals(self::VALID_IS_DONE_VALUE, $task->getIsDone());
        $this->assertInstanceOf(DateTime::class, $task->getCreatedAt());

        //Number of errors awaited = 1
        $errors = $this->getValidationErrors($task, 1);

        //Return of message = message assert from entity
        $this->assertEquals(self::NOT_BLANK_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }

    /**
     * Test Task Invalid because no Content
     * 
     */
    public function testTaskIsInvalidBecauseNoContent(): void
    {
        $task = new Task();

        //Verify setters
        $task->setTitle(self::VALID_TITLE_VALUE)
            ->setIsDone(self::VALID_IS_DONE_VALUE)
            ->setCreatedAt(new DateTime());

        //Verify getters
        $this->assertEquals(self::VALID_TITLE_VALUE, $task->getTitle());
        $this->assertEquals(self::VALID_IS_DONE_VALUE, $task->getIsDone());
        $this->assertInstanceOf(DateTime::class, $task->getCreatedAt());

        //Number of errors awaited = 1
        $errors = $this->getValidationErrors($task, 1);

        //Return of message = message assert from entity
        $this->assertEquals(self::NOT_BLANK_CONSTRAINT_MESSAGE, $errors[0]->getMessage());
    }

    /**
     * Test getter and setter for Author
     */
    public function testValidAuthor(): void
    {
        $user = new User();
        $task = new Task();
        $task->setAuthor($user);
        $this->assertEquals($user, $task->getAuthor());
    }

    public function testValidToggle(): void
    {
        $task = new Task();
        $task->toggle(true);
        $this->assertTrue($task->getIsDone());
    }

    /**
     * Management of errors
     * 
     */
    private function getValidationErrors(Task $task, int $numberOfExpectedErrors): ConstraintViolationList
    {
        $errors = $this->validator->validate($task);

        $this->assertCount($numberOfExpectedErrors, $errors);

        return $errors;
    }
}