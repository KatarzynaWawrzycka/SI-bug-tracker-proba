<?php

namespace App\Service;

use App\Entity\Bug;
use App\Entity\Comment;
use App\Repository\CommentRepository;

/**
 * Class CommentService.
 */
class CommentService implements CommentServiceInterface
{
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    public function findByBug(Bug $bug): array
    {
        return $this->commentRepository->findByBug($bug);
    }
}
