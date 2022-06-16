<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="task_list")
     * 
     * Affiche la liste des tasks pas faites
     */
    public function listAction(TaskRepository $taskRepository)
    {
        $tasks = $taskRepository->findBy(['isDone' => false]);

        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @Route("/tasks/done", name="task_list_isDone")
     * 
     * Affiche la liste des tasks faites
     */
    public function listActionisDone(TaskRepository $taskRepository)
    {
        $tasks = $taskRepository->findBy(['isDone' => true]);

        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * 
     * Créer une task
     */
    public function createAction(Request $request, EntityManagerInterface $em)
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')))
                ->setIsDone(false)
                ->setAuthor($this->getAuthor());

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * 
     * Modifier une task
     */
    public function editAction(Task $task, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

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
     * Affiche une task comme faite
     */
    public function toggleTaskAction(Task $task, EntityManagerInterface $em)
    {
        $task->toggle(!$task->getIsDone());

        $em->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * 
     * Effacer une task
     */
    public function deleteTaskAction(Task $task, EntityManagerInterface $em)
    {
        $user = $this->getAuthor()->getRoles();

        if ($task->getAuthor() === null && $user[0] == "ROLE_ADMIN" || $task->getAuthor() == $this->getAuthor()) {
            $em->remove($task);
            $em->flush();

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