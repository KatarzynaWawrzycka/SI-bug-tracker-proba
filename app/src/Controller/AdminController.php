<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserEmailType;
use App\Form\Type\UserPasswordType;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class AdminController extends AbstractController
{
    #[Route(name: 'user_index')]
    public function index(Request $request, UserServiceInterface $userService): Response
    {
        if (!$this->isGranted('USER_INDEX')) {
            $this->addFlash('warning', 'Access denied.');
            return $this->redirectToRoute('bug_index');
        }

        $pagination = $userService->getPaginatedUsers($request);

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'user_show')]
    public function show(User $user): Response
    {
        if (!$this->isGranted('USER_SHOW', $user)) {
            $this->addFlash('warning', 'Access denied.');
            return $this->redirectToRoute('bug_index');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit-email', name: 'user_edit_email')]
    public function editEmail(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('USER_EDIT_EMAIL', $user)) {
            $this->addFlash('warning', 'Access denied.');
            return $this->redirectToRoute('bug_index');
        }

        $form = $this->createForm(UserEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Email updated successfully.');

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit_email.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit-password', name: 'user_edit_password')]
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('USER_EDIT_PASSWORD', $user)) {
            $this->addFlash('warning', 'Access denied.');
            return $this->redirectToRoute('bug_index');
        }

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($plainPassword !== $confirmPassword) {
                $form->addError(new FormError('Passwords do not match.'));
            } else {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->flush();

                $this->addFlash('success', 'Password updated successfully.');

                return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
