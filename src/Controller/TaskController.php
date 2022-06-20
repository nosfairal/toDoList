<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    public function __construct(TaskRepository $repository, EntityManagerInterface $manager)
    {
       $this->repository = $repository ;
       $this->manager = $manager ;
    }
    /**
     * @Route("/tasks", name="task_list")
     * 
     * Display undone tasks list
     */
    public function listAction()
    {
        $tasks = $this->repository->findBy(['isDone' => false]);

        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @Route("/tasks/done", name="task_list_isDone")
     * 
     * Display done tasks list
     */
    public function listActionisDone()
    {
        $tasks = $this->repository->findBy(['isDone' => true]);

        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * 
     * Create a task
     */
    public function createAction(Request $request)
    {
        $task = new Task();
        $user = $this->getUser();
        dd($user);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')))
                ->setIsDone(false)
                ->setAuthor($this->getUser());

            $this->repository->add($task);

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * 
     * Update a task
     */
    public function editAction(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            if ($task->getIsDone() === true) {
                return $this->redirectToRoute('task_list_isDone');
            }
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * 
     * Toggle task to done
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->GetIsDone());

        $this->manager->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * 
     * Delete a  task
     */
    public function deleteTaskAction(Task $task)
    {
        $user = $task->getAuthor();//->getRoles();
\dd($user);
        if ($task->getAuthor() === null && $user[0] == "ROLE_ADMIN" || $task->getAuthor() == $this->getUser()) {
            $this->repository->remove($task);

            $this->addFlash('success', 'La tâche a bien été supprimée.');
        } else {
            $this->addFlash('error', "Vous n'avez pas le droit de supprimer cette tâche !");
            return $this->redirectToRoute('task_list');
        }

        if ($task->getIsDone() === true) {
            return $this->redirectToRoute('task_list_isDone');
        }
        return $this->redirectToRoute('task_list');
    }
}