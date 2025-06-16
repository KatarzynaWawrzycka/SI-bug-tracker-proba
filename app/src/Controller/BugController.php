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
use App\Service\BugServiceInterface;
use App\Service\CategoryServiceInterface;
use App\Service\CommentServiceInterface;
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
     */
    public function __construct(private readonly BugServiceInterface $bugService, private readonly TranslatorInterface $translator, private readonly CategoryServiceInterface $categoryServiceInterface, private readonly CommentServiceInterface $commentServiceInterface)
    {
    }

    /**
     * Index action.
     *
     * @param BugListInputFiltersDto $filters
     * @param int                    $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'bug_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: BugListInputFiltersDtoResolver::class)] BugListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->bugService->getPaginatedList($page, null, $filters);

        $categories = $this->categoryServiceInterface->findAll();

        return $this->render('bug/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
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
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'bug_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $bug = new Bug();
        $bug->setAuthor($user);
        $form = $this->createForm(BugType::class, $bug);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bugService->save($bug);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('bug_index');
        }

        return $this->render(
            'bug/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Bug     $bug     Bug entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'bug_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Bug $bug): Response
    {
        if (!$this->isGranted(BugVoter::EDIT, $bug)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('record_not_found')
            );

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
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bugService->save($bug);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('bug_index');
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
