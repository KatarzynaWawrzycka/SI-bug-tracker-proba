<?php

/**
 * Profile Controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserEmailType;
use App\Form\Type\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProfileController.
 */
#[Route('/profile')]
class ProfileController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param TranslatorInterface         $translator     Translator
     * @param UserPasswordHasherInterface $passwordHasher Password Hasher
     * @param EntityManagerInterface      $entityManager  Entity Manager
     */
    public function __construct(private readonly TranslatorInterface $translator, private readonly UserPasswordHasherInterface $passwordHasher, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(name: 'profile', methods: ['GET'])]
    public function profile(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit email action.
     *
     * @param Request $request Request
     */
    #[Route('/email', name: 'edit_email', methods: ['GET', 'POST'])]
    public function editEmail(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('message.email_updated'));

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/edit_email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit password action.
     *
     * @param Request $request Request
     */
    #[Route('/password', name: 'edit_password', methods: ['GET', 'POST'])]
    public function editPassword(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if (empty($plainPassword) || empty($confirmPassword)) {
                $form->get('confirmPassword')->addError(new FormError(''));
            } elseif ($plainPassword !== $confirmPassword) {
                $form->get('confirmPassword')->addError(new FormError('Passwords don\'t match'));
            } else {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('message.password_updated'));

                return $this->redirectToRoute('profile');
            }
        }

        return $this->render('profile/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
