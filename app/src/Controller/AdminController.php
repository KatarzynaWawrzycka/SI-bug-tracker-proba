<?php

/**
 * Admin Controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\AdminEmailType;
use App\Form\Type\AdminPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AdminController.
 */
#[Route('/admin')]
class AdminController extends AbstractController
{
    /**
     * Constructor.
     * @param TranslatorInterface         $translator
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface      $entityManager
     */
    public function __construct(private readonly TranslatorInterface $translator, private readonly UserPasswordHasherInterface $passwordHasher, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return Response
     */
    #[Route(name: 'admin_profile', methods: ['GET'])]
    public function profile(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('admin/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit email action.
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/email', name: 'admin_edit_email', methods: ['GET', 'POST'])]
    public function editEmail(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(AdminEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('message.email_updated'));

            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('admin/edit_email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit password action.
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/password', name: 'admin_edit_password', methods: ['GET', 'POST'])]
    public function editPassword(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(AdminPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if (empty($plainPassword) || empty($confirmPassword)) {
                $form->get('confirmPassword')->addError(new FormError('Oba pola hasła muszą być wypełnione.'));
            } elseif ($plainPassword !== $confirmPassword) {
                $form->get('confirmPassword')->addError(new FormError('Hasła nie są takie same.'));
            } else {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('message.password_updated'));

                return $this->redirectToRoute('admin_profile');
            }
        }

        return $this->render('admin/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
