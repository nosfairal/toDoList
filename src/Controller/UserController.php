<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(UserRepository $repository, EntityManagerInterface $manager)
    {
       $this->repository = $repository ;
       $this->manager = $manager ;
    }
    /**
     * @Route("/users", name="user_list")
     * @IsGranted("ROLE_ADMIN", message="Vous devez être authentifié comme administrateur pour voir cette page.")
     */
    public function listUsersAction()
    {
        $users = $this->repository->findAll();
        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function createUserAction(Request $request, UserPasswordHasherInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
            $redirectRoute = 'login';

            // Add ROLE_ADMIN to user roles if admin checkbox is checked 
            if ($this->isGranted("ROLE_ADMIN")) {
                ($form->get('admin')->getData()) ? $user->setRoles(['ROLE_ADMIN']) : $user->setRoles([]);
                $redirectRoute = 'user_list';
            }

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', "Le compte utilisateur a bien été créé.");

            return $this->redirectToRoute($redirectRoute);
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     * @IsGranted("USER_EDIT", subject="user", message="Vous ne pouvez modifier que votre propre compte utilisateur.")
     */
    public function editUserAction(User $user, Request $request, UserPasswordHasherInterface $encoder)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
            $redirectRoute = 'homepage';

            // Add/Remove ROLE_ADMIN to/from user roles if admin checkbox is/isn't checked
            if ($this->isGranted("ROLE_ADMIN")) {
                ($form->get('admin')->getData()) ? $user->setRoles(['ROLE_ADMIN']) : $user->setRoles([]);
                $redirectRoute = 'user_list';
            }

            $this->manager->flush();

            $this->addFlash('success', "Le compte utilisateur a bien été modifié");

            return $this->redirectToRoute($redirectRoute);
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * @Route("/users/{id}/delete", name="user_delete")
     * @IsGranted("USER_DELETE", subject="user", message="Vous n'avez pas les droits pour supprimer des utilisateurs.")
     */
    public function deleteUserAction(User $user)
    {
        $this->manager->remove($user);
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');

        return $this->redirectToRoute('user_list');
    }
}