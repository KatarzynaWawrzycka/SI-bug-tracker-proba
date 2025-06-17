<?php

namespace App\Service;

use App\Entity\Bug;
use App\Entity\Comment;

/**
 * Interface CommentServiceInterface.
 */
interface CommentServiceInterface
{
    /**
     * Find comments by bug.
     *
     * @param Bug $bug
     *
     * @return Comment[]
     */
    public function findByBug(Bug $bug): array;

    public function findOneById(int $id): ?Comment;
}
