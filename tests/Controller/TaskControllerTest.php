<?php

namespace App\Tests;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    /**
     * constant represent a email with role is USER
     */
    protected const EMAIL_USER1 = 'user1@hotmail.com';

    /**
     * constant represent a email with role is USER
     */
    protected const EMAIL_USER2 = 'user2@hotmail.com';

    /**
     * constant represent an email with role is ADMIN
     */
    protected const EMAIL_ADMIN = 'admin@hotmail.com';

    /**
     * constant that represents the title of the task
     */
    protected const TASK_TITLE = 'test create Tasks';

    /**
     * constant that represents the content of the task
     */
    protected const TASK_CONTENT = 'contenu de tache 4';


    /**
     * constant represent a task of user1
     */
    protected const TASK_ID_AUTHOR1 = 4;

    /**
     * constant represent a task without author
     */
    protected const TASK_ID_AUTHORNULL = 1;

    /**
     * constant represent a task with isDone status
     */
    protected const TASK_ID_IS_DONE = 5;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test display tasks list without logged
     */
    public function testListTasksNoLogged(): void
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('label', 'Mot de passe');
    }

    /**
     * Test display tasks list by user logged
     */
    public function testListTasksWithRoleUSER()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test Create a task by user unlogged
     */
    public function testCreateTasksNoLogged(): void
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('label', 'Mot de passe :');
    }

    /**
     * Test Create a task by user logged
     */
    public function testCreateTaskWithRoleUSER()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);
        $this->client->loginUser($testUser);

        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('label', 'Title');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = self::TASK_TITLE;
        $form['task[content]'] = self::TASK_CONTENT;

        $crawler = $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! La tâche a été bien été ajoutée.");
        $this->assertNotNull($taskRepository->findOneBy(['title' => self::TASK_TITLE]));
    }

    /**
     * Test Delete a task  by an user unlogged
     */
    public function testDeleteTasksNoLogged(): void
    {
        $this->client->request('GET', '/tasks/' . self::TASK_ID_AUTHOR1 . '/delete');

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('label', 'Mot de passe :');
    }

    /**
     * Test Delete a task without author by an user logged
     */
    public function testDeleteTaskWithNoAuthor()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER2);

        $this->client->loginUser($testUser);

        // $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/tasks/' . self::TASK_ID_AUTHOR1 . '/delete');

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();


        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert.alert-danger', "VOUS AVEZ ETE REDIRIGE SUR CETTE PAGE CAR : cette tache ne vous appartient pas ou vous n'etes pas admin ce site, vous n'avez donc pas le droit de la supprimer");

        $this->assertNotNull($taskRepository->find(self::TASK_ID_AUTHOR1));
    }

    /**
     * Test Edit a task undone by user unlogged
     */
    public function testEditasksNoLogged(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $authorTask = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $task = $taskRepository->findOneBy(['author' => $authorTask]);

        $idTaskEdit = $task->getId();

        $this->client->request('GET', "/tasks/$idTaskEdit/edit");

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('label', 'Mot de passe :');
    }

    /**
     * Test Edit a task undone with an user logged
     */
    public function testEditasksUserLogged()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $task = $taskRepository->findOneBy(['author' => $testUser]);

        $idTaskEdit = $task->getId();

        $this->client->loginUser($testUser);

        // $this->client->followRedirects();

        $crawler = $this->client->request('GET', "/tasks/$idTaskEdit/edit");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'task[content]' => self::TASK_CONTENT
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! La tâche a bien été modifiée.");
        $this->assertNotNull($taskRepository->findOneBy(['id' => $idTaskEdit]));
        $taskUpdated = $taskRepository->findOneBy(['id' => $idTaskEdit]);
        $this->assertSame($taskUpdated->getContent(), self::TASK_CONTENT);
    }

    /**
     * Test Edit a task done with an user logged
     */
    public function testEditaskUndoneUserLogged()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $task = $taskRepository->findOneBy(['isDone' => $testUser]);

        $idTaskEdit = $task->getId();

        $this->client->loginUser($testUser);

        // $this->client->followRedirects();

        $crawler = $this->client->request('GET', "/tasks/$idTaskEdit/edit");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'task[content]' => self::TASK_CONTENT
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks/done', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! La tâche a bien été modifiée.");
        $this->assertNotNull($taskRepository->findOneBy(['id' => $idTaskEdit]));
        $taskUpdated = $taskRepository->findOneBy(['id' => $idTaskEdit]);
        $this->assertSame($taskUpdated->getContent(), self::TASK_CONTENT);
    }

    /**
     * Test Toggle a task without logged
     */
    public function testToggleTasksNoLogged(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $AuthorTask = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $task = $taskRepository->findOneBy(['author' => $AuthorTask]);

        $idTaskToggle = $task->getId();

        $this->client->request('GET', "/tasks/$idTaskToggle/toggle");

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('label', 'Mot de passe :');
    }

    /**
     * Test Toggle a task by an user logged
     */
    public function testToggleaTaskUserLogged(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $task = $taskRepository->findOneBy(['author' => $testUser]);

        $idTaskToggle = $task->getId();

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', "/tasks/$idTaskToggle/toggle");

        $this->assertResponseRedirects('/tasks', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! La tâche ma tache a bien été marquée comme faite.");
    }

    /**
     * Test Delete a task undone with an author
     */
    public function testDeleteTaskUndoneWithAuthor()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $this->client->loginUser($testUser);

        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/tasks/' . self::TASK_ID_AUTHOR1 . '/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! La tâche a bien été supprimée.");
        $this->assertNull($taskRepository->find(self::TASK_ID_AUTHOR1));
    }

    /**
     * Test Delete a task done with an author
     */
    public function testDeleteTaskDoneWithAuthor()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $this->client->loginUser($testUser);

        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/tasks/' . self::TASK_ID_IS_DONE . '/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! La tâche a bien été supprimée.");
        $this->assertNull($taskRepository->find(self::TASK_ID_IS_DONE));
    }

    /**
     * Test Delete a tasw without author by user
     */
    public function testDeleteTaskAuthorNullByUSER()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testUser = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $this->client->loginUser($testUser);
        //\dd($testUser);
        $crawler = $this->client->request('GET', '/tasks/' . self::TASK_ID_AUTHORNULL . '/delete');

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert.alert-danger', "VOUS AVEZ ETE REDIRIGE SUR CETTE PAGE CAR : cette tache ne vous appartient pas ou vous n'etes pas admin ce site, vous n'avez donc pas le droit de la supprimer");
        $this->assertNotNull($taskRepository->find(self::TASK_ID_AUTHORNULL));
    }

    /**
     * Test Delete a tasw without author by admin
     */
    public function testDeleteTaskAuthorNullByADMIN()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        $testAdmin = $userRepository->findOneByEmail(self::EMAIL_ADMIN);

        $this->client->loginUser($testAdmin);

        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/tasks/' . self::TASK_ID_AUTHORNULL . '/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success', "Superbe ! La tâche a bien été supprimée.");
        $this->assertNull($taskRepository->find(self::TASK_ID_AUTHORNULL));
    }

    /**
     * Test Display tasks list done
     */
    public function testDisplayTasksIsDone()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testAdmin = $userRepository->findOneByEmail(self::EMAIL_USER1);

        $this->client->loginUser($testAdmin);

        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/tasks/done');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('button', "Marquée non terminée");
    }
}
