<?php

/**
 * Bug controller.
 */

namespace App\Controller;

use App\Dto\BugListInputFiltersDto;
use App\Entity\Bug;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\Type\BugType;
use App\Form\Type\CommentType;
use App\Resolver\BugListInputFiltersDtoResolver;
use App\Security\Voter\BugVoter;
use App\Security\Voter\CommentVoter;
use App\Service\BugServiceInterface;
use App\Service\CategoryServiceInterface;
use App\Service\TagServiceInterface;
use App\Service\CommentServiceInterface;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BugController.
 */
#[Route('/bug')]
class BugController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param BugServiceInterface      $bugService               Bug service
     * @param TranslatorInterface      $translator               Translator
     * @param CategoryServiceInterface $categoryServiceInterface Category service interface
     * @param TagServiceInterface      $tagServiceInterface      Tag service interface
     * @param CommentServiceInterface  $commentServiceInterface  Comment service interface
     */
    public function __construct(private readonly BugServiceInterface $bugService, private readonly TranslatorInterface $translator, private readonly CategoryServiceInterface $categoryServiceInterface, private readonly TagServiceInterface $tagServiceInterface, private readonly CommentServiceInterface $commentServiceInterface)
    {
    }

    /**
     * Index action.
     *
     * @param BugListInputFiltersDto $filters Filters
     * @param int                    $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'bug_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: BugListInputFiltersDtoResolver::class)] BugListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->bugService->getPaginatedList($page, null, $filters);
        $categories = $this->categoryServiceInterface->findAll();
        $tags = $this->tagServiceInterface->findAll();

        return $this->render('bug/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Show action.
     *
     * @param Bug $bug Bug entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'bug_show', requirements: ['id' => '[1-9]\d*'], methods: 'GET')]
    public function show(Bug $bug): Response
    {
        $comments = $this->commentServiceInterface->findByBug($bug);

        return $this->render('bug/show.html.twig', [
            'bug' => $bug,
            'comments' => $comments,
        ]);
    }

    /**
     * Comment action.
     *
     * @param Request                $request       HTTP request
     * @param Bug                    $bug           Bug
     * @param EntityManagerInterface $entityManager Entity manager
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/comment', name: 'bug_comment', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function comment(Request $request, Bug $bug, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', $this->translator->trans('access_denied'));

            return $this->redirectToRoute('bug_show', ['id' => $bug->getId()]);
        }

        $comment = new Comment();
        $comment->setBug($bug);
        $comment->setAuthor($user);
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('message.comment_added'));

            return $this->redirectToRoute('bug_show', ['id' => $bug->getId()]);
        }

        return $this->render('bug/comment.html.twig', [
            'form' => $form->createView(),
            'bug' => $bug,
        ]);
    }

    /**
     * Edit comment action.
     *
     * @param Request                $request       HTTP request
     * @param int                    $bugId         Bug
     * @param int                    $commentId     Comment
     * @param EntityManagerInterface $entityManager Entity manager
     *
     * @return Response HTTP response
     */
    #[Route('/{bugId}/comment/{commentId}/edit', name: 'bug_comment_edit', requirements: ['bugId' => '\d+', 'commentId' => '\d+'], methods: ['GET', 'POST'])]
    public function editComment(Request $request, int $bugId, int $commentId, EntityManagerInterface $entityManager): Response
    {
        $comment = $this->commentServiceInterface->findOneById($commentId);
        $bug = $this->bugService->findOneById($bugId);

        if (!$comment || !$bug || $comment->getBug()->getId() !== $bug->getId()) {
            $this->addFlash('warning', $this->translator->trans('message.record_not_found'));

            return $this->redirectToRoute('bug_show', ['id' => $bugId]);
        }

        if (!$this->isGranted(CommentVoter::EDIT, $comment)) {
            $this->addFlash('warning', $this->translator->trans('access_denied'));

            return $this->redirectToRoute('bug_show', ['id' => $bugId]);
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('message.comment_updated'));

            return $this->redirectToRoute('bug_show', ['id' => $bugId]);
        }

        return $this->render('bug/comment_edit.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'bug' => $bug,
        ]);
    }

    /**
     * Delete comment action.
     *
     * @param Request                $request       HTTP request
     * @param int                    $bugId         Bug
     * @param int                    $commentId     Comment
     * @param EntityManagerInterface $entityManager Entity manager
     *
     * @return Response HTTP response
     */
    #[Route('/{bugId}/comment/{commentId}/delete', name: 'bug_comment_delete', requirements: ['bugId' => '\d+', 'commentId' => '\d+'], methods: ['GET', 'DELETE'])]
    public function deleteComment(Request $request, int $bugId, int $commentId, EntityManagerInterface $entityManager): Response
    {
        $comment = $this->commentServiceInterface->findOneById($commentId);
        $bug = $this->bugService->findOneById($bugId);

        if (!$comment || !$bug || $comment->getBug()->getId() !== $bug->getId()) {
            $this->addFlash('warning', $this->translator->trans('message.record_not_found'));

            return $this->redirectToRoute('bug_show', ['id' => $bugId]);
        }

        if (!$this->isGranted(CommentVoter::DELETE, $comment)) {
            $this->addFlash('warning', $this->translator->trans('access_denied'));

            return $this->redirectToRoute('bug_show', ['id' => $bugId]);
        }

        $form = $this->createForm(FormType::class, $comment, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('bug_comment_delete', [
                'bugId' => $bug->getId(),
                'commentId' => $comment->getId(),
            ]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('message.comment_deleted'));

            return $this->redirectToRoute('bug_show', ['id' => $bug->getId()]);
        }

        return $this->render('bug/comment_delete.html.twig', [
            'form' => $form->createView(),
            'bug' => $bug,
            'comment' => $comment,
        ]);
    }

    /**
     * Close bug action.
     *
     * @param Request $request HTTP request
     * @param Bug     $bug     Bug
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/close', name: 'bug_close', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST|PUT')]
    public function close(Request $request, Bug $bug): Response
    {
        if (!$this->isGranted('BUG_CLOSE', $bug)) {
            $this->addFlash('warning', $this->translator->trans('access_denied'));

            return $this->redirectToRoute('bug_show', ['id' => $bug->getId()]);
        }

        $form = $this->createForm(
            FormType::class,
            $bug,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('bug_close', ['id' => $bug->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bugService->close($bug);

            $this->addFlash('success', $this->translator->trans('message.bug_closed_successfully'));

            return $this->redirectToRoute('bug_show', ['id' => $bug->getId()]);
        }

        return $this->render('bug/close.html.twig', [
            'form' => $form->createView(),
            'bug' => $bug,
        ]);
    }

    /**
     * Archive closed bug action.
     *
     * @param Request $request HTTP request
     * @param Bug     $bug     Bug
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/archive', name: 'bug_archive', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST', 'PUT'])]
    public function archive(Request $request, Bug $bug): Response
    {
        if (!$this->isGranted('BUG_ARCHIVE', $bug)) {
            $this->addFlash('warning', $this->translator->trans('access_denied'));

            return $this->redirectToRoute('bug_show', ['id' => $bug->getId()]);
        }

        $form = $this->createForm(
            FormType::class,
            $bug,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('bug_archive', ['id' => $bug->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bugService->archive($bug);

            $this->addFlash('success', $this->translator->trans('message.bug_archived_successfully'));

            return $this->redirectToRoute('bug_show', ['id' => $bug->getId()]);
        }

        return $this->render('bug/archive.html.twig', [
            'form' => $form->createView(),
            'bug' => $bug,
        ]);
    }

    /**
     * Create action.
     *
     * @param Request              $request     HTTP request
     * @param UserServiceInterface $userService User service interface
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'bug_create', methods: 'GET|POST')]
    public function create(Request $request, UserServiceInterface $userService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $bug = new Bug();
        $bug->setAuthor($user);
        $form = $this->createForm(BugType::class, $bug);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = $form->get('assignedToEmail')->getData();

            if ($email) {
                $assignedUser = $userService->findOneByEmail($email);
                if ($assignedUser) {
                    $bug->setAssignedTo($assignedUser);
                } else {
                    $form->get('assignedToEmail')->addError(
                        new \Symfony\Component\Form\FormError('User not fount')
                    );
                }
            }

            if ($form->isValid()) {
                $this->bugService->save($bug);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('bug_index');
            }
        }

        return $this->render(
            'bug/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request              $request     HTTP request
     * @param Bug                  $bug         Bug entity
     * @param UserServiceInterface $userService User service interface
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'bug_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Bug $bug, UserServiceInterface $userService): Response
    {
        if (!$this->isGranted(BugVoter::EDIT, $bug)) {
            $this->addFlash('warning', $this->translator->trans('record_not_found'));

            return $this->redirectToRoute('bug_index');
        }

        $form = $this->createForm(
            BugType::class,
            $bug,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('bug_edit', ['id' => $bug->getId()]),
            ]
        );

        if ($bug->getAssignedTo()) {
            $form->get('assignedToEmail')->setData($bug->getAssignedTo()->getEmail());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = $form->get('assignedToEmail')->getData();

            if ($email) {
                $assignedUser = $userService->findOneByEmail($email);
                if ($assignedUser) {
                    $bug->setAssignedTo($assignedUser);
                } else {
                    $form->get('assignedToEmail')->addError(
                        new \Symfony\Component\Form\FormError('User not found')
                    );
                }
            } else {
                $bug->setAssignedTo(null);
            }

            if ($form->isValid()) {
                $this->bugService->save($bug);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.edited_successfully')
                );

                return $this->redirectToRoute('bug_index');
            }
        }

        return $this->render(
            'bug/edit.html.twig',
            [
                'form' => $form->createView(),
                'bug' => $bug,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Bug     $bug     Bug entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'bug_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Bug $bug): Response
    {
        if (!$this->isGranted(BugVoter::DELETE, $bug)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found')
            );

            return $this->redirectToRoute('bug_index');
        }

        $form = $this->createForm(
            FormType::class,
            $bug,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('bug_delete', ['id' => $bug->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bugService->delete($bug);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('bug_index');
        }

        return $this->render(
            'bug/delete.html.twig',
            [
                'form' => $form->createView(),
                'bug' => $bug,
            ]
        );
    }
}
