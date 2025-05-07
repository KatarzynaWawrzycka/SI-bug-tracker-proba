<?php
/**
 * Admin controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\AdminProfileType;
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
     * @param TranslatorInterface $translator
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(private readonly TranslatorInterface $translator, private readonly UserPasswordHasherInterface $passwordHasher, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Edit action.
     *
     * @param Request $request
     * @return Response HTTP response
     */
    #[Route(name: 'admin_profile', methods: ['GET', 'POST'])]
    public function editProfile(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AdminProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($plainPassword || $confirmPassword) {
                if (empty($plainPassword) || empty($confirmPassword)) {
                    $form->get('confirmPassword')->addError(new FormError('Both password fields must be filled.'));
                } elseif ($plainPassword !== $confirmPassword) {
                    $form->get('confirmPassword')->addError(new FormError('Passwords do not match.'));
                } else {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }
            }

            if (!$form->get('confirmPassword')->getErrors(true)->count()) {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

                return $this->redirectToRoute('admin_profile');
            }
        }

        return $this->render('admin/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
