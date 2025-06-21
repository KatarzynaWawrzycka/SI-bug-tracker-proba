<?php

/**
 * Comment service.
 */

namespace App\Service;

use App\Entity\Bug;
use App\Entity\Comment;
use App\Repository\CommentRepository;

/**
 * Class CommentService.
 */
class CommentService implements CommentServiceInterface
{
    /**
     * Constructor.
     *
     * @param CommentRepository $commentRepository Comment repository
     */
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    /**
     * Find comments by bug.
     *
     * @param Bug $bug Bug
     *
     * @return Comment[]
     */
    public function findByBug(Bug $bug): array
    {
        return $this->commentRepository->findByBug($bug);
    }

    /**
     * Find comment by id.
     *
     * @param int $id Id
     *
     * @return Comment|null Comment
     */
    public function findOneById(int $id): ?Comment
    {
        return $this->commentRepository->find($id);
    }
}
